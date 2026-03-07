# ============================================================
#  EXACT PATH : LRRS\ai\equipment_analyzer.py
#
#  Saves JSON to:  LRRS\ai\equipment_usage_report.json
#  PHP reads that JSON to get usage %
#
#  INSTALL : pip install mysql-connector-python
# ============================================================

import sys
import io
sys.stdout = io.TextIOWrapper(sys.stdout.buffer, encoding="utf-8", errors="replace")

import mysql.connector
import re
import json
import os
from collections import defaultdict
from datetime import datetime

# ── DB CONFIG ──────────────────────────────────────────────
DB_CONFIG = {
    "host":     "localhost",
    "user":     "root",
    "password": "root",
    "database": "lab_db",
    "port":     3306
}

# ── OUTPUT JSON saved in SAME folder as this script ────────
# This script is at:  LRRS\ai\equipment_analyzer.py
# JSON will save to:  LRRS\ai\equipment_usage_report.json
OUTPUT_JSON = os.path.join(
    os.path.dirname(os.path.abspath(__file__)),
    "equipment_usage_report.json"
)

# ── CONNECT ────────────────────────────────────────────────
def connect_db():
    try:
        return mysql.connector.connect(**DB_CONFIG)
    except mysql.connector.Error as e:
        print(f"[AI ERROR] DB connection failed: {e}")
        raise SystemExit(1)

# ── FETCH equipment.name ───────────────────────────────────
def fetch_equipment_names(cursor):
    cursor.execute(
        "SELECT name FROM equipment "
        "WHERE name IS NOT NULL AND TRIM(name) != '' "
        "ORDER BY equipment_id"
    )
    names = [row[0].strip() for row in cursor.fetchall()]
    print(f"[AI] Equipment names: {names}")
    return names

# ── FETCH reservation.comment + any_comment ────────────────
def fetch_comments(cursor):
    cursor.execute(
        "SELECT reservation_id, comment, any_comment FROM reservation"
    )
    rows = cursor.fetchall()
    print(f"[AI] Reservation rows: {len(rows)}")
    return rows

# ── AI SMART MATCHER ───────────────────────────────────────
def ai_match(text, equipment_names):
    if not text or not str(text).strip():
        return []

    text_lower = str(text).lower()
    found      = []

    for name in equipment_names:
        name_lower = name.lower().strip()
        words      = name_lower.split()

        # 1 — direct substring
        if name_lower in text_lower:
            found.append(name); continue

        # 2 — plural / singular
        variants = {
            name_lower + "s",
            name_lower + "es",
            name_lower.rstrip("s"),
            name_lower.rstrip("es")
        }
        if any(v in text_lower for v in variants):
            found.append(name); continue

        # 3 — word-boundary regex
        escaped = r"\s+".join(re.escape(w) for w in words)
        if re.search(r"\b" + escaped + r"\b", text_lower):
            found.append(name); continue

        # 4 — all meaningful words present
        if len(words) > 1:
            long_words = [w for w in words if len(w) >= 3]
            if long_words and all(
                re.search(r"\b" + re.escape(w) + r"\b", text_lower)
                for w in long_words
            ):
                found.append(name); continue

        # 5 — abbreviation
        if len(words) >= 2:
            abbrev = "".join(w[0] for w in words)
            if len(abbrev) >= 2 and re.search(
                r"\b" + re.escape(abbrev) + r"\b", text_lower
            ):
                found.append(name); continue

    return list(set(found))

# ── ANALYSE ────────────────────────────────────────────────
def analyse(rows, equipment_names):
    usage_count = defaultdict(int)
    total       = len(rows)

    for res_id, comment, any_comment in rows:
        combined = " | ".join(
            str(c) for c in [comment, any_comment] if c is not None
        )
        for eq in ai_match(combined, equipment_names):
            usage_count[eq] += 1

    return usage_count, total

# ── SAVE JSON ──────────────────────────────────────────────
def save_json(usage_count, equipment_names, total_rows):
    sorted_eq = sorted(
        equipment_names,
        key=lambda e: usage_count.get(e, 0),
        reverse=True
    )

    results = []
    for rank, name in enumerate(sorted_eq, 1):
        count = usage_count.get(name, 0)
        usage = round((count / total_rows * 100), 2) if total_rows > 0 else 0.0
        results.append({
            "rank":           rank,
            "equipment_name": name,
            "mention_count":  count,
            "usage_percent":  usage
        })
        print(f"[AI]   {name:<30} mentions={count}  usage={usage}%")

    report = {
        "generated_at":    datetime.now().strftime("%Y-%m-%d %H:%M:%S"),
        "database":        DB_CONFIG["database"],
        "table_scanned":   "reservation",
        "columns_scanned": ["comment", "any_comment"],
        "total_rows":      total_rows,
        "formula":         "usage_percent = mention_count / total_rows * 100",
        "equipment_usage": results
    }

    with open(OUTPUT_JSON, "w", encoding="utf-8") as f:
        json.dump(report, f, indent=2, ensure_ascii=False)

    print(f"[AI] JSON saved → {OUTPUT_JSON}")

# ── MAIN ───────────────────────────────────────────────────
def main():
    print("[AI] Starting analysis...")
    conn   = connect_db()
    cursor = conn.cursor()

    names              = fetch_equipment_names(cursor)
    rows               = fetch_comments(cursor)
    usage_count, total = analyse(rows, names)
    save_json(usage_count, names, total)

    cursor.close()
    conn.close()
    print(f"[AI] Done. {len(names)} equipment | {total} rows scanned.")

if __name__ == "__main__":
    main()
