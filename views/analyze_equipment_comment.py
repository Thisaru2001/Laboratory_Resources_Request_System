#!/usr/bin/env python3
"""
LRRS/views/analyze_equipment_comment.py
Find the LAST student who used a specific piece of equipment
by searching reservation comments for the equipment name.

DB schema facts:
  - usage_log.approval_hods_id  → FK to approval_hods.approval_hods_id
  - approval_hods.is_approved = 1  means HOD approved (session finished)
  - reservation.comment  contains equipment names e.g. "Centrifuge (1 unit)..."
  - equipment.lab_id = NULL for all rows (cannot use for join)
"""

import sys, json, re
import mysql.connector

from dotenv import load_dotenv
import os

load_dotenv()  # loads .env file

B_CONFIG = {
    "host": os.getenv("DB_HOST"),
    "user": os.getenv("DB_USER"),
    "password": os.getenv("DB_PASS"),
    "database": os.getenv("DB_NAME"),
    "port": int(os.getenv("DB_PORT"))
}

POSITIVE_WORDS = {
    "good","great","excellent","fine","working","clean","perfect",
    "okay","ok","well","nice","properly","smooth","functional",
    "complete","done","finished","clear","tidy","safe","normal"
}
NEGATIVE_WORDS = {
    "broken","damage","damaged","fault","faulty","issue","problem",
    "error","fail","failed","scratch","scratched","missing","loose",
    "stuck","dirty","messy","cracked","burned","overheating","slow",
    "noise","noisy","leak","leaking","bad","wrong","dead","spark",
    "smoke","smell","corrupt","corrupted","not working"
}

def clean_text(text):
    if not text:
        return []
    text = text.lower()
    text = re.sub(r'[^\w\s]', ' ', text)
    return [t for t in text.split() if len(t) > 1]

def get_sentiment(tokens):
    pos   = sum(1 for t in tokens if t in POSITIVE_WORDS)
    neg   = sum(1 for t in tokens if t in NEGATIVE_WORDS)
    total = pos + neg
    if total == 0:
        return "Neutral"
    return "Positive" if pos / total >= 0.6 else ("Negative" if pos / total <= 0.4 else "Neutral")

def get_keywords(tokens, top_n=5):
    stop = {"the","and","for","was","are","has","have","that","this",
            "with","used","use","unit","pcs","sets","set","need","will"}
    freq = {}
    for t in tokens:
        if t not in stop and len(t) > 2:
            freq[t] = freq.get(t, 0) + 1
    return [k for k, _ in sorted(freq.items(), key=lambda x: x[1], reverse=True)[:top_n]]

def comment_mentions_equipment(comment_text, equip_name, equip_code):
    if not comment_text:
        return False, 0
    text_lower = comment_text.lower()

    # 1. Exact equipment code match (highest confidence)
    if equip_code and equip_code.lower() in text_lower:
        return True, 3

    if equip_name:
        name_lower = equip_name.lower()
        # Remove parenthetical: "Microscope (Compound)" → "Microscope"
        base_name = re.sub(r'\s*\(.*?\)', '', name_lower).strip()

        # 2. Full base name match
        if base_name and base_name in text_lower:
            return True, 2

        # 3. All significant words match
        words = [w for w in base_name.split() if len(w) > 3]
        if words:
            matched = sum(1 for w in words if w in text_lower)
            if matched == len(words):
                return True, 1
            # At least first significant word
            if text_lower.find(words[0]) != -1:
                return True, 1

    return False, 0

def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No equipment_code provided"}))
        sys.exit(1)

    equipment_code = sys.argv[1]

    try:
        conn   = mysql.connector.connect(**DB_CONFIG)
        cursor = conn.cursor(dictionary=True)
    except Exception as e:
        print(json.dumps({"error": f"DB connection failed: {str(e)}"}))
        sys.exit(1)

    try:
        # Step 1: Get equipment name by code
        cursor.execute("""
            SELECT equipment_id, equipment_code, name
            FROM equipment
            WHERE equipment_code = %s
            LIMIT 1
        """, (equipment_code,))
        equip = cursor.fetchone()

        if not equip:
            print(json.dumps({"error": f"Equipment not found: {equipment_code}"}))
            sys.exit(1)

        equip_name = equip["name"]

        # Step 2: Get ALL HOD-approved (finished) reservations
        # approval_hods.is_approved = 1 means fully approved/finished
        cursor.execute("""
            SELECT
                r.reservation_id,
                r.reservation_id_generate,
                r.student_id,
                lu.university_id,
                lu.first_name,
                lu.last_name,
                r.comment      AS booking_comment,
                r.any_comment  AS extra_comment,
                r.datetime_of_booking,
                r.request_date,
                ul.usage_log_id
            FROM usage_log ul
            INNER JOIN approval_hods ah
                    ON ah.approval_hods_id = ul.approval_hods_id
            INNER JOIN reservation r
                    ON r.reservation_id = ul.lab_booking_id
            INNER JOIN lab_user lu
                    ON lu.user_id = r.student_id
            WHERE ah.is_approved = 1
            ORDER BY ul.usage_log_id DESC
        """)
        all_rows = cursor.fetchall()

    except Exception as e:
        print(json.dumps({"error": f"Query failed: {str(e)}"}))
        sys.exit(1)
    finally:
        cursor.close()
        conn.close()

    if not all_rows:
        print(json.dumps({
            "student_id":     None,
            "university_id":  None,
            "full_name":      None,
            "reservation_id": None,
            "confidence":     0.0,
            "sentiment":      "Unknown",
            "keywords":       [],
            "analysis":       "No completed usage records found.",
            "raw_comment":    "",
            "mention_found":  False
        }))
        sys.exit(0)

    # Step 3: Score rows — does comment mention this equipment?
    scored = []
    for row in all_rows:
        combined = " | ".join(filter(None, [
            row["booking_comment"] or "",
            row["extra_comment"]   or ""
        ])).strip()

        found, score = comment_mentions_equipment(combined, equip_name, equipment_code)
        if found:
            scored.append((score, row, combined))

   
    if scored:
        scored.sort(key=lambda x: x[0], reverse=True)
        _, best_row, best_comment = scored[0]
        confidence    = round(min(0.5 + scored[0][0] * 0.2, 1.0), 2)
        mention_found = True
    else:
        
        best_row     = all_rows[0]
        best_comment = " | ".join(filter(None, [
            best_row["booking_comment"] or "",
            best_row["extra_comment"]   or ""
        ])).strip()
        confidence    = 0.3
        mention_found = False

    tokens    = clean_text(best_comment)
    sent      = get_sentiment(tokens)
    kws       = get_keywords(tokens)
    neg_found = list(set(t for t in tokens if t in NEGATIVE_WORDS))
    pos_found = list(set(t for t in tokens if t in POSITIVE_WORDS))

    display_id  = best_row["university_id"] or f"User #{best_row['student_id']}"
    display_res = best_row["reservation_id_generate"] or str(best_row["reservation_id"])
    full_name   = f"{best_row['first_name'] or ''} {best_row['last_name'] or ''}".strip()
    req_date    = str(best_row["request_date"] or best_row["datetime_of_booking"] or "")

    if not mention_found:
        analysis = (
            f"No reservation explicitly mentions '{equip_name}'. "
            f"Showing last completed lab session by {display_id}"
            + (f" ({full_name})" if full_name else "") + "."
        )
    elif neg_found:
        analysis = (
            f"Last used by {display_id}"
            + (f" ({full_name})" if full_name else "")
            + f" on {req_date}. Possible issues noted: {', '.join(neg_found)}."
        )
    elif pos_found:
        analysis = (
            f"Last used by {display_id}"
            + (f" ({full_name})" if full_name else "")
            + f" on {req_date}. Equipment in good condition."
        )
    else:
        analysis = (
            f"Last used by {display_id}"
            + (f" ({full_name})" if full_name else "")
            + f" on {req_date}."
        )

    print(json.dumps({
        "student_id":     best_row["student_id"],
        "university_id":  display_id,
        "full_name":      full_name,
        "reservation_id": display_res,
        "confidence":     confidence,
        "sentiment":      sent,
        "keywords":       kws,
        "analysis":       analysis,
        "raw_comment":    best_comment,
        "mention_found":  mention_found
    }))

if __name__ == "__main__":
    main()
