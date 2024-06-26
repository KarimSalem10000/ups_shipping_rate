import base64
import requests
from flask import request, jsonify, make_response
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
            response_data = response.json()
            return make_response(jsonify(response_data), 200)
        else:
            response_data = {
                'error': 'Failed to retrieve token',
                'status_code': response.status_code,
                'response': response.text
            }
            return make_response(jsonify(response_data), response.status_code)


@fedex_ns.route('/rate-quote')
class RateQuote(Resource):
    def post(self):
        data = request.json
        service_type = data.get('service_type', 'FEDEX_GROUND')  # Default to FEDEX_GROUND

        # Fetch FedEx access token
        access_token = get_fedex_token()

        if not access_token:
            response_data = {"message": "Failed to retrieve FedEx access token"}
            return make_response(jsonify(response_data), 500)

        # Request payload for rate quotes
        payload = {
            "accountNumber": {"value": "740561073"},
            "rateRequestControlParameters": {"returnTransitTimes": True},
            "requestedShipment": {
                "shipper": {
                    "address": {
                        "streetLines": [data['originStreet'], data['originApt']],
                        "city": data['originCity'],
                        "stateOrProvinceCode": data['originState'],
                        "postalCode": data['originZip'],
                        "countryCode": "US"
                    }
                },
                "recipient": {
                    "address": {
                        "streetLines": [data['destStreet'], data['destApt']],
                        "city": data['destCity'],
                        "stateOrProvinceCode": data['destState'],
                        "postalCode": data['destZip'],
                        "countryCode": "US"
                    }
                },
                "pickupType": "DROPOFF_AT_FEDEX_LOCATION",
                "rateRequestType": ["ACCOUNT"],
                "requestedPackageLineItems": [{
                    "groupPackageCount": 1,
                    "weight": {"units": "LB", "value": float(data['weight'])},
                    "dimensions": {
                        "length": float(data['packageLength']),
                        "width": float(data['packageWidth']),
                        "height": float(data['packageHeight']),
                        "units": "IN"
                    }
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
            response_data = {"message": "Failed to get rate quotes", "error": response.json()}
            return make_response(jsonify(response_data), response.status_code)

        rate_details = response.json().get('output', {}).get('rateReplyDetails', [])

        for rate_detail in rate_details:
            if rate_detail['serviceType'] == service_type:
                total_cost = rate_detail['ratedShipmentDetails'][0]['totalNetCharge']
                response_data = {
                    "service_type": service_type,
                    "total_cost": total_cost
                }
                return make_response(jsonify(response_data), 200)

        response_data = {"message": f"Service type {service_type} not found in the response"}
        return make_response(jsonify(response_data), 404)


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
