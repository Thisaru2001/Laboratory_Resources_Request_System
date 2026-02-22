# controllers/simple_ai_checker.py
import pymysql
from datetime import datetime, timedelta
from flask import jsonify, session
import logging

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

class SimpleAvailabilityAI:
    def __init__(self, host='localhost', user='root', password='root', database='lab_db'):
        self.db_config = {
            'host': host,
            'user': user,
            'password': password,
            'database': database,
            'charset': 'utf8mb4',
            'cursorclass': pymysql.cursors.DictCursor
        }
    
    def _get_connection(self):
        return pymysql.connect(**self.db_config)
    
    def get_student_name(self, user_id):
        conn = self._get_connection()
        try:
            with conn.cursor() as cursor:
                cursor.execute("SELECT first_name FROM lab_user WHERE user_id = %s", (user_id,))
                user = cursor.fetchone()
                return user['first_name'] if user else "Student"
        except:
            return "Student"
        finally:
            conn.close()
    
    def check_availability(self, equipment_list, lab_location_name, request_date, 
                          continue_days, start_time, end_time, user_id=None):
        
        student_name = "Student"
        if user_id:
            student_name = self.get_student_name(user_id)
        
        # Calculate datetime range
        start_datetime = datetime.strptime(f"{request_date} {start_time}", "%Y-%m-%d %H:%M")
        
        if continue_days > 1:
            end_date = datetime.strptime(request_date, "%Y-%m-%d") + timedelta(days=continue_days-1)
            end_datetime = datetime.strptime(f"{end_date.strftime('%Y-%m-%d')} {end_time}", "%Y-%m-%d %H:%M")
        else:
            end_datetime = datetime.strptime(f"{request_date} {end_time}", "%Y-%m-%d %H:%M")
        
        conn = self._get_connection()
        unavailable_reasons = []
        all_available = True
        
        try:
            with conn.cursor() as cursor:
                
                # Check Lab Availability
                lab_query = """
                    SELECT l.location, s.status as lab_status
                    FROM lab_location l
                    JOIN status_of_lab_location s ON l.status_of_lab_location_status_of_lab_location_id = s.status_of_lab_location_id
                    WHERE l.location = %s
                """
                cursor.execute(lab_query, (lab_location_name,))
                lab = cursor.fetchone()
                
                if not lab:
                    return {
                        'available': False,
                        'message': f"❌ Sorry {student_name}, the lab '{lab_location_name}' was not found.",
                        'details': ['Lab location not found']
                    }
                
                if lab['lab_status'] != 'Available':
                    return {
                        'available': False,
                        'message': f"❌ Sorry {student_name}, {lab_location_name} is currently {lab['lab_status']}.",
                        'details': [f"Lab status: {lab['lab_status']}"]
                    }
                
                # Check each equipment
                for item in equipment_list:
                    equipment_id = item['equipment_id']
                    requested_qty = item['qty']
                    
                    equip_query = """
                        SELECT 
                            e.name,
                            e.equipment_id,
                            COUNT(ue.unique_equipment_id) as total_units,
                            SUM(CASE WHEN soe.status = 'Available' THEN 1 ELSE 0 END) as available
                        FROM equipment e
                        LEFT JOIN unique_equipment ue ON e.equipment_id = ue.equipment_equipment_id
                        LEFT JOIN status_of_equipment soe ON ue.status_of_equipment_status_id = soe.status_id
                        WHERE e.equipment_id = %s
                        GROUP BY e.equipment_id
                    """
                    cursor.execute(equip_query, (equipment_id,))
                    equip = cursor.fetchone()
                    
                    if not equip:
                        unavailable_reasons.append(f"Equipment ID {equipment_id} not found")
                        all_available = False
                        continue
                    
                    # Check reservations
                    reserve_query = """
                        SELECT COUNT(DISTINCT uehr.unique_equipment_unique_equipment_id) as reserved,
                               GROUP_CONCAT(DISTINCT CONCAT(u.first_name, ' ', u.last_name) SEPARATOR ', ') as reserved_by
                        FROM reservation r
                        JOIN unique_equipment_has_reservation uehr ON r.reservation_id = uehr.reservation_reservation_id
                        JOIN unique_equipment ue ON uehr.unique_equipment_unique_equipment_id = ue.unique_equipment_id
                        LEFT JOIN lab_user u ON r.student_id = u.user_id
                        WHERE ue.equipment_equipment_id = %s
                        AND r.status IN ('Approved', 'Pending')
                        AND (
                            (r.start_datetime <= %s AND r.end_datetime >= %s) OR
                            (r.start_datetime <= %s AND r.end_datetime >= %s) OR
                            (r.start_datetime >= %s AND r.end_datetime <= %s)
                        )
                    """
                    cursor.execute(reserve_query, (
                        equipment_id, end_datetime, start_datetime,
                        end_datetime, start_datetime, start_datetime, end_datetime
                    ))
                    reserve_data = cursor.fetchone()
                    reserved = reserve_data['reserved'] or 0
                    reserved_by = reserve_data['reserved_by'] or "someone"
                    
                    # Check maintenance
                    maint_query = """
                        SELECT COUNT(*) as maintenance
                        FROM equipment_maintenance em
                        WHERE em.equipment_id = %s
                        AND em.status_of_maintenance_id IN (1, 2)
                    """
                    cursor.execute(maint_query, (equipment_id,))
                    maintenance = cursor.fetchone()['maintenance'] or 0
                    
                    # Check damaged
                    damaged_query = """
                        SELECT COUNT(*) as damaged
                        FROM unique_equipment ue
                        JOIN status_of_equipment soe ON ue.status_of_equipment_status_id = soe.status_id
                        WHERE ue.equipment_equipment_id = %s
                        AND soe.status = 'Damaged'
                    """
                    cursor.execute(damaged_query, (equipment_id,))
                    damaged = cursor.fetchone()['damaged'] or 0
                    
                    truly_available = equip['available'] - reserved - maintenance
                    
                    if requested_qty > truly_available:
                        all_available = False
                        
                        if reserved > 0:
                            reason = f"{equip['name']} is booked by {reserved_by}"
                        elif maintenance > 0:
                            reason = f"{equip['name']} is under maintenance"
                        elif damaged > 0:
                            reason = f"{equip['name']} is damaged"
                        else:
                            reason = f"Only {truly_available} {equip['name']} available"
                        
                        unavailable_reasons.append(reason)
                
                if all_available:
                    return {
                        'available': True,
                        'message': f"✅ Good news {student_name}! All equipment is available for your request.",
                        'details': []
                    }
                else:
                    if len(unavailable_reasons) == 1:
                        msg = f"❌ Sorry {student_name}, {unavailable_reasons[0].lower()}"
                    else:
                        items = []
                        for r in unavailable_reasons[:2]:
                            parts = r.split(' is')
                            items.append(parts[0] if len(parts) > 1 else r.split(' Only')[0])
                        
                        if len(unavailable_reasons) > 2:
                            msg = f"❌ Sorry {student_name}, {items[0]}, {items[1]} and {len(unavailable_reasons)-2} other item(s) have issues"
                        else:
                            msg = f"❌ Sorry {student_name}, {items[0]} and {items[1]} have availability issues"
                    
                    return {
                        'available': False,
                        'message': msg,
                        'details': unavailable_reasons
                    }
                
        except Exception as e:
            logger.error(f"Error: {str(e)}")
            return {
                'available': False,
                'message': f"❌ Sorry {student_name}, a system error occurred. Please try again.",
                'details': [str(e)]
            }
        finally:
            conn.close()

def setup_availability_route(app):
    @app.route('/api/check-availability', methods=['POST'])
    def check_availability():
        try:
            data = request.json
            user_id = session.get('user_id')
            
            ai = SimpleAvailabilityAI(
                host='localhost',
                user='root',
                password='root',
                database='lab_db'
            )
            
            result = ai.check_availability(
                equipment_list=data.get('equipment', []),
                lab_location_name=data.get('lab_location'),
                request_date=data.get('request_date'),
                continue_days=int(data.get('continue_days', 1)),
                start_time=data.get('start_time'),
                end_time=data.get('end_time'),
                user_id=user_id
            )
            
            return jsonify({
                'success': True,
                'available': result['available'],
                'message': result['message'],
                'details': result['details']
            })
            
        except Exception as e:
            return jsonify({
                'success': False,
                'message': f"Error: {str(e)}"
            }), 500