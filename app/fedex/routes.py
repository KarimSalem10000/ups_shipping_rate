import base64
import requests
from flask import request, jsonify
from flask_restx import Resource, Namespace
from . import fedex_ns

@fedex_ns.route('/token')
class FedExToken(Resource):
    def post(self):
        url = 'https://apis-sandbox.fedex.com/oauth/token'
        payload = {
            'grant_type': 'client_credentials',
            'client_id': 'l7cd15a6eddf8b4b13b827f12d3b7e8b50',
            'client_secret': '45069dacbfa94424a5ad592b8f4ecbb8'
        }
        headers = {
            'Content-Type': 'application/x-www-form-urlencoded'
        }
        
        response = requests.post(url, data=payload, headers=headers)
        
        if response.status_code == 200:
            return response.json()
        else:
            return {
                'error': 'Failed to retrieve token',
                'status_code': response.status_code,
                'response': response.text
            }, response.status_code


@fedex_ns.route('/rate-quote')
class RateQuote(Resource):
    def post(self):
        # Ensure the request Content-Type is application/json
        if request.content_type != 'application/json':
            return jsonify({"message": "Content-Type must be application/json"}), 415

        # Fetch request data
        data = request.json
        service_type = data.get('service_type', 'FEDEX_GROUND')  # Default to FEDEX_GROUND

        # Fetch FedEx access token
        access_token = get_fedex_token()

        if not access_token:
            return jsonify({"message": "Failed to retrieve FedEx access token"}), 500

        # Request payload for rate quotes
        payload = {
            "accountNumber": {"value": "740561073"},
            "rateRequestControlParameters": {"returnTransitTimes": True},
            "requestedShipment": {
                "shipper": {"address": {"postalCode": "94105", "countryCode": "US"}},
                "recipient": {"address": {"postalCode": "10001", "countryCode": "US"}},
                "pickupType": "DROPOFF_AT_FEDEX_LOCATION",
                "shipmentRateDetail": {"rateType": ["LIST"], "rateScale": "PL"},
                "requestedPackageLineItems": [{
                    "groupPackageCount": 1,
                    "weight": {"units": "LB", "value": 10},
                    "dimensions": {"length": 10, "width": 10, "height": 10, "units": "IN"}
                }]
            }
        }

        response = requests.post(
            'https://apis-sandbox.fedex.com/rate/v1/rates/quotes',
            headers={
                'Authorization': f'Bearer {access_token}',
                'Content-Type': 'application/json',
                'X-locale': 'en_US'
            },
            json=payload
        )

        if response.status_code != 200:
            return jsonify({"message": "Failed to get rate quotes", "error": response.json()}), response.status_code

        rate_details = response.json().get('output', {}).get('rateReplyDetails', [])

        for rate_detail in rate_details:
            if rate_detail['serviceType'] == service_type:
                total_cost = rate_detail['ratedShipmentDetails'][0]['totalNetCharge']
                return jsonify({
                    "service_type": service_type,
                    "total_cost": total_cost
                })

        return jsonify({"message": f"Service type {service_type} not found in the response"}), 404


def get_fedex_token():
    # Set your application credentials
    client_id = 'l7cd15a6eddf8b4b13b827f12d3b7e8b50'
    client_secret = '45069dacbfa94424a5ad592b8f4ecbb8'

    # Encode the credentials
    credentials = base64.b64encode(f"{client_id}:{client_secret}".encode('utf-8')).decode('utf-8')

    # Prepare the payload
    payload = {'grant_type': 'client_credentials'}

    # Set the headers
    headers = {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Authorization': f'Basic {credentials}'
    }

    # Make the request
    response = requests.post('https://apis-sandbox.fedex.com/oauth/token', data=payload, headers=headers)

    if response.status_code == 200:
        response_data = response.json()
        return response_data.get('access_token')
    else:
        return None
