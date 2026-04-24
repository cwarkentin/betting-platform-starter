"""
Sports Data Ingestion Microservice

This service fetches sports data from external APIs and updates the events table.

TODO: Build this after Laravel API is working!

Your tasks:
1. Create Flask app with health check endpoint
2. Add database connection using SQLAlchemy
3. Create endpoint to fetch sports data
4. Transform and insert into events table
5. Add error handling
6. Write tests with pytest
"""

from flask import Flask

app = Flask(__name__)


@app.route('/health')
def health():
    """Health check endpoint"""
    return {'status': 'healthy', 'service': 'sports-data-ingestion'}, 200


if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)
