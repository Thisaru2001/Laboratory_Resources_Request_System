# app.py
from flask import Flask, request, jsonify, session
from flask_cors import CORS
from datetime import datetime, timedelta
import pymysql
from simple_ai_checker import setup_availability_route

app = Flask(__name__)
app.config['SECRET_KEY'] = 'your-secret-key-here'
CORS(app, supports_credentials=True)

# Database connection function
def get_db_connection():
    return pymysql.connect(
        host='localhost',
        user='root',
        password='root',  # Your MySQL password
        database='lab_db',
        cursorclass=pymysql.cursors.DictCursor
    )

# ============ LOGIN ENDPOINT ============
@app.route('/api/login', methods=['POST'])
def login():
    data = request.json
    email = data.get('email')
    password = data.get('password')
    
    conn = get_db_connection()
    try:
        with conn.cursor() as cursor:
            query = """
                SELECT u.*, ur.role_id, r.role
                FROM lab_user u
                JOIN user_has_role ur ON u.user_id = ur.user_id
                JOIN role r ON ur.role_id = r.role_id
                WHERE u.email = %s AND u.status = 1
            """
            cursor.execute(query, (email,))
            user = cursor.fetchone()
            
            # For demo, using plain text (use proper password verification in production)
            if user and password == 'Subodi@123':
                session['user_id'] = user['user_id']
                session['role'] = user['role']
                
                return jsonify({
                    'success': True,
                    'user': {
                        'id': user['user_id'],
                        'name': f"{user['first_name']} {user['last_name']}",
                        'email': user['email'],
                        'role': user['role']
                    }
                })
            else:
                return jsonify({'success': False, 'message': 'Invalid credentials'})
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)})
    finally:
        conn.close()

# ============ LAB LOCATIONS ENDPOINT ============
@app.route('/api/lab-locations', methods=['GET'])
def get_lab_locations():
    conn = get_db_connection()
    try:
        with conn.cursor() as cursor:
            query = """
                SELECT l.lab_location_id, l.location, s.status as lab_status
                FROM lab_location l
                JOIN status_of_lab_location s ON l.status_of_lab_location_status_of_lab_location_id = s.status_of_lab_location_id
                WHERE l.block = 0 AND s.status = 'Available'
                ORDER BY l.location
            """
            cursor.execute(query)
            locations = cursor.fetchall()
            
            return jsonify({
                'success': True,
                'locations': locations
            })
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)})
    finally:
        conn.close()

# ============ EQUIPMENT SEARCH ENDPOINT ============
@app.route('/api/equipment/search', methods=['GET'])
def search_equipment():
    search_term = request.args.get('term', '')
    
    conn = get_db_connection()
    try:
        with conn.cursor() as cursor:
            query = """
                SELECT e.equipment_id, e.name, e.equipment_code,
                       COUNT(ue.unique_equipment_id) as total_units,
                       SUM(CASE WHEN soe.status = 'Available' THEN 1 ELSE 0 END) as available_units
                FROM equipment e
                LEFT JOIN unique_equipment ue ON e.equipment_id = ue.equipment_equipment_id
                LEFT JOIN status_of_equipment soe ON ue.status_of_equipment_status_id = soe.status_id
                WHERE e.name LIKE %s OR e.equipment_code LIKE %s
                GROUP BY e.equipment_id
                HAVING total_units > 0
                LIMIT 10
            """
            cursor.execute(query, (f'%{search_term}%', f'%{search_term}%'))
            equipment = cursor.fetchall()
            
            return jsonify({
                'success': True,
                'equipment': equipment
            })
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)})
    finally:
        conn.close()

# ============ SUBMIT REQUEST ENDPOINT ============
@app.route('/api/submit-request', methods=['POST'])
def submit_request():
    if 'user_id' not in session:
        return jsonify({'success': False, 'message': 'Please login first'}), 401
    
    data = request.json
    equipment_list = data.get('equipment', [])
    lab_location = data.get('lab_location')
    request_date = data.get('request_date')
    continue_days = int(data.get('continue_days', 1))
    start_time = data.get('start_time')
    end_time = data.get('end_time')
    comment = data.get('comment', '')
    
    conn = get_db_connection()
    try:
        with conn.cursor() as cursor:
            # Get lab_location_id
            cursor.execute("SELECT lab_location_id FROM lab_location WHERE location = %s", (lab_location,))
            lab = cursor.fetchone()
            if not lab:
                return jsonify({'success': False, 'message': 'Invalid lab location'})
            lab_location_id = lab['lab_location_id']
            
            # Parse datetimes
            start_datetime = datetime.strptime(f"{request_date} {start_time}", "%Y-%m-%d %H:%M")
            
            if continue_days > 1:
                end_date = datetime.strptime(request_date, "%Y-%m-%d") + timedelta(days=continue_days-1)
                end_datetime = datetime.strptime(f"{end_date.strftime('%Y-%m-%d')} {end_time}", "%Y-%m-%d %H:%M")
            else:
                end_datetime = datetime.strptime(f"{request_date} {end_time}", "%Y-%m-%d %H:%M")
            
            # Create approval_hods entry
            cursor.execute("""
                INSERT INTO approval_hods (is_approved, approved_datetime, reason)
                VALUES (NULL, NULL, 'Pending student request')
            """)
            approval_hods_id = cursor.lastrowid
            
            # Create reservation
            cursor.execute("""
                INSERT INTO reservation (
                    student_id, lab_location_id, is_supersisor_approved,
                    datetime_of_booking, request_date, start_datetime,
                    end_datetime, status, comment, is_equipment_ready,
                    approval_hods_id, is_rejected_by_technical_officer
                ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
            """, (
                session['user_id'], lab_location_id, 0,
                datetime.now(), request_date, start_datetime,
                end_datetime, 'Pending', comment, 0,
                approval_hods_id, 0
            ))
            reservation_id = cursor.lastrowid
            
            conn.commit()
            
            return jsonify({
                'success': True,
                'message': 'Request submitted successfully',
                'reservation_id': reservation_id
            })
            
    except Exception as e:
        conn.rollback()
        return jsonify({'success': False, 'message': str(e)})
    finally:
        conn.close()

# ============ MY REQUESTS ENDPOINT ============
@app.route('/api/my-requests', methods=['GET'])
def get_my_requests():
    if 'user_id' not in session:
        return jsonify({'success': False, 'message': 'Please login first'}), 401
    
    conn = get_db_connection()
    try:
        with conn.cursor() as cursor:
            query = """
                SELECT 
                    r.reservation_id,
                    GROUP_CONCAT(DISTINCT e.name SEPARATOR ', ') as equipment_names,
                    COUNT(DISTINCT uehr.unique_equipment_unique_equipment_id) as total_equipment,
                    r.request_date,
                    DATE_FORMAT(r.start_datetime, '%%H:%%i') as start_time,
                    DATE_FORMAT(r.end_datetime, '%%H:%%i') as end_time,
                    l.location as lab_name,
                    r.status,
                    r.comment,
                    r.rejected_reason
                FROM reservation r
                JOIN lab_location l ON r.lab_location_id = l.lab_location_id
                LEFT JOIN unique_equipment_has_reservation uehr ON r.reservation_id = uehr.reservation_reservation_id
                LEFT JOIN unique_equipment ue ON uehr.unique_equipment_unique_equipment_id = ue.unique_equipment_id
                LEFT JOIN equipment e ON ue.equipment_equipment_id = e.equipment_id
                WHERE r.student_id = %s
                GROUP BY r.reservation_id
                ORDER BY r.request_date DESC, r.start_datetime DESC
            """
            cursor.execute(query, (session['user_id'],))
            requests = cursor.fetchall()
            
            return jsonify({
                'success': True,
                'requests': requests
            })
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)})
    finally:
        conn.close()

# ============ LOGOUT ENDPOINT ============
@app.route('/api/logout', methods=['POST'])
def logout():
    session.clear()
    return jsonify({'success': True, 'message': 'Logged out successfully'})

# ============ SETUP AI AVAILABILITY CHECKER ============
setup_availability_route(app)

if __name__ == '__main__':
    print("="*50)
    print("🚀 Starting Flask Server")
    print("="*50)
    print("\n📝 Available endpoints:")
    print("   POST /api/login")
    print("   GET  /api/lab-locations")
    print("   GET  /api/equipment/search?term=")
    print("   POST /api/check-availability")
    print("   POST /api/submit-request")
    print("   GET  /api/my-requests")
    print("   POST /api/logout")
    print("\n🌐 Server running at: http://localhost:5000")
    print("="*50)
    
    app.run(debug=True, port=5000)