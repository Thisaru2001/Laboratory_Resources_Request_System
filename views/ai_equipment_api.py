from flask import Flask, request, jsonify
from flask_cors import CORS
import pymysql
import pandas as pd
import numpy as np
from sklearn.feature_extraction.text import CountVectorizer
from sklearn.naive_bayes import MultinomialNB
import re
from datetime import datetime, timedelta
import joblib
import os

app = Flask(__name__)
CORS(app)  # Enable CORS for PHP requests

# Database configuration
DB_CONFIG = {
    'host': 'localhost',
    'user': 'root',
    'password': '',
    'database': 'microbiology_lab',
    'charset': 'utf8mb4'
}

class EquipmentAnalyzer:
    def __init__(self):
        self.connection = None
        self.model = None
        self.vectorizer = None
        
    def connect_db(self):
        """Establish database connection"""
        try:
            self.connection = pymysql.connect(**DB_CONFIG)
            return True
        except Exception as e:
            print(f"Database connection error: {e}")
            return False
    
    def get_equipment_data(self):
        """Fetch equipment and reservation data"""
        cursor = self.connection.cursor(pymysql.cursors.DictCursor)
        
        # Get all equipment
        cursor.execute("""
            SELECT e.equipment_id, e.equipment_code, e.equipment_name, e.qty as total_qty,
                   e.description, e.location
            FROM equipment e
            WHERE e.status = 'active'
        """)
        equipment = cursor.fetchall()
        
        # Get reservation comments and usage data
        cursor.execute("""
            SELECT 
                r.reservation_id,
                r.comment,
                r.any_comment,
                r.status,
                r.request_date,
                r.start_time,
                r.end_time,
                rd.qty as requested_qty,
                e.equipment_id,
                e.equipment_name
            FROM reservation r
            LEFT JOIN reservation_details rd ON r.reservation_id = rd.reservation_id
            LEFT JOIN equipment e ON rd.equipment_id = e.equipment_id
            WHERE r.status IN ('Approved', 'Completed')
            AND r.request_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
            ORDER BY r.request_date DESC
        """)
        reservations = cursor.fetchall()
        
        return equipment, reservations
    
    def analyze_comments(self, reservations):
        """Analyze reservation comments to determine usage patterns"""
        comments_data = []
        
        for res in reservations:
            # Combine both comment fields
            full_comment = f"{res.get('comment', '')} {res.get('any_comment', '')}".strip()
            
            if full_comment and res.get('equipment_name'):
                comments_data.append({
                    'equipment': res['equipment_name'],
                    'comment': full_comment,
                    'date': res['request_date'],
                    'qty': res.get('requested_qty', 1)
                })
        
        return comments_data
    
    def calculate_usage_percentage(self, equipment, reservations, comments_data):
        """Calculate usage percentage using AI/ML logic"""
        results = []
        
        for eq in equipment:
            eq_id = eq['equipment_id']
            eq_name = eq['equipment_name']
            total_qty = eq['total_qty']
            
            # Filter reservations for this equipment
            eq_reservations = [r for r in reservations if r.get('equipment_id') == eq_id]
            eq_comments = [c for c in comments_data if c['equipment'] == eq_name]
            
            # Calculate base usage from reservation count
            reservation_count = len(eq_reservations)
            comment_count = len(eq_comments)
            
            # Calculate time-based factors
            if eq_reservations:
                # Get most recent reservation
                latest_date = max([r['request_date'] for r in eq_reservations if r['request_date']])
                days_since_last = (datetime.now() - latest_date).days if latest_date else 365
            else:
                days_since_last = 365
            
            # Calculate comment sentiment (simplified)
            positive_keywords = ['good', 'excellent', 'working', 'great', 'perfect', 'like']
            negative_keywords = ['broken', 'issue', 'problem', 'not working', 'repair', 'maintenance']
            
            positive_score = 0
            negative_score = 0
            
            for comment in eq_comments:
                comment_text = comment['comment'].lower()
                positive_score += sum(1 for word in positive_keywords if word in comment_text)
                negative_score += sum(1 for word in negative_keywords if word in comment_text)
            
            # AI/ML Formula for usage percentage
            # Factors: reservation frequency, recency, comment sentiment, quantity requested
            
            # Base usage from reservations (max 50 points)
            usage_score = min(50, (reservation_count / 10) * 50)
            
            # Recency factor (max 20 points)
            if days_since_last < 7:
                recency_score = 20
            elif days_since_last < 30:
                recency_score = 15
            elif days_since_last < 90:
                recency_score = 10
            elif days_since_last < 180:
                recency_score = 5
            else:
                recency_score = 0
            
            # Comment sentiment factor (max 20 points)
            total_comments = comment_count or 1
            sentiment_score = min(20, ((positive_score - negative_score) / total_comments) * 10 + 10)
            
            # Quantity factor (max 10 points)
            if eq_reservations:
                avg_qty = np.mean([r.get('requested_qty', 1) for r in eq_reservations])
                qty_score = min(10, (avg_qty / total_qty) * 10)
            else:
                qty_score = 0
            
            # Calculate final percentage
            usage_percentage = min(100, max(0, usage_score + recency_score + sentiment_score + qty_score))
            
            # Determine status color
            if usage_percentage >= 70:
                color = '#22c55e'  # Green - High usage
            elif usage_percentage >= 40:
                color = '#f59e0b'  # Orange - Medium usage
            else:
                color = '#ef4444'  # Red - Low usage
            
            # Get maintenance count
            cursor = self.connection.cursor()
            cursor.execute("""
                SELECT COUNT(*) as maintenance_count 
                FROM equipment_maintenance 
                WHERE equipment_id = %s AND status = 'pending'
            """, (eq_id,))
            maintenance_count = cursor.fetchone()[0]
            
            results.append({
                'equipment_id': eq_id,
                'equipment_code': eq['equipment_code'],
                'equipment_name': eq_name,
                'total_qty': total_qty,
                'available': total_qty - maintenance_count,
                'maintenance': maintenance_count,
                'usage_percentage': round(usage_percentage, 1),
                'color': color,
                'reservation_count': reservation_count,
                'comment_count': comment_count,
                'last_used_days': days_since_last
            })
        
        return results
    
    def train_model(self, historical_data):
        """Optional: Train ML model on historical data"""
        # This is for future enhancement - would require labeled training data
        pass

@app.route('/api/analyze-equipment', methods=['GET'])
def analyze_equipment():
    """Main API endpoint for equipment analysis"""
    analyzer = EquipmentAnalyzer()
    
    if not analyzer.connect_db():
        return jsonify({'success': False, 'message': 'Database connection failed'})
    
    try:
        equipment, reservations = analyzer.get_equipment_data()
        comments_data = analyzer.analyze_comments(reservations)
        usage_data = analyzer.calculate_usage_percentage(equipment, reservations, comments_data)
        
        return jsonify({
            'success': True,
            'data': usage_data,
            'total_equipment': len(equipment),
            'analyzed_at': datetime.now().isoformat()
        })
    
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)})
    
    finally:
        if analyzer.connection:
            analyzer.connection.close()

@app.route('/api/equipment/<int:equipment_id>', methods=['GET'])
def get_equipment_details(equipment_id):
    """Get detailed analysis for specific equipment"""
    analyzer = EquipmentAnalyzer()
    
    if not analyzer.connect_db():
        return jsonify({'success': False, 'message': 'Database connection failed'})
    
    try:
        cursor = analyzer.connection.cursor(pymysql.cursors.DictCursor)
        
        # Get equipment details
        cursor.execute("""
            SELECT e.*, 
                   COUNT(DISTINCT rd.reservation_id) as total_reservations,
                   AVG(rd.qty) as avg_quantity
            FROM equipment e
            LEFT JOIN reservation_details rd ON e.equipment_id = rd.equipment_id
            WHERE e.equipment_id = %s
            GROUP BY e.equipment_id
        """, (equipment_id,))
        
        equipment = cursor.fetchone()
        
        # Get recent reservations with comments
        cursor.execute("""
            SELECT r.comment, r.any_comment, r.request_date, rd.qty
            FROM reservation r
            JOIN reservation_details rd ON r.reservation_id = rd.reservation_id
            WHERE rd.equipment_id = %s
            AND r.status IN ('Approved', 'Completed')
            ORDER BY r.request_date DESC
            LIMIT 20
        """, (equipment_id,))
        
        recent_usage = cursor.fetchall()
        
        return jsonify({
            'success': True,
            'equipment': equipment,
            'recent_usage': recent_usage
        })
        
    except Exception as e:
        return jsonify({'success': False, 'message': str(e)})
    
    finally:
        if analyzer.connection:
            analyzer.connection.close()

if __name__ == '__main__':
    app.run(host='127.0.0.1', port=5000, debug=True)