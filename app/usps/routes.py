import requests
from flask import request, jsonify
from flask_restx import Namespace, Resource
import logging
from . import usps_ns as api
import xml.etree.ElementTree as ET

# Setup logging
logging.basicConfig(level=logging.INFO)
logger = logging.getLogger(__name__)

# Function to get OAuth Token
def get_oauth_token(consumer_key, consumer_secret):
    token_url = "https://api.usps.com/oauth2/v3/token"
    post_data = {
        'grant_type': 'client_credentials',
        'client_id': consumer_key,
        'client_secret': consumer_secret
    }

    response = requests.post(token_url, data=post_data, headers={'Content-Type': 'application/x-www-form-urlencoded'})

    if response.status_code != 200:
        logger.error(f"Failed to get OAuth token: {response.status_code} {response.text}")
        return None, response.status_code, response.text

    response_data = response.json()
    return response_data.get('access_token'), response.status_code, response.text

# Function to get shipping rates
def get_shipping_rates(access_token, user_id, package_details, service):
    url = "http://production.shippingapis.com/ShippingAPI.dll"

    xml_request = f"""
    <RateV4Request USERID="{user_id}">
        <Revision>2</Revision>
        <Package ID="0">
            <Service>{service}</Service>
            <ZipOrigination>{package_details['ZipOrigination']}</ZipOrigination>
            <ZipDestination>{package_details['ZipDestination']}</ZipDestination>
            <Pounds>{package_details['Pounds']}</Pounds>
            <Ounces>{package_details['Ounces']}</Ounces>
            <Container></Container>
            <Machinable>TRUE</Machinable>
        </Package>
    </RateV4Request>
    """

    params = {
        'API': 'RateV4',
        'xml': xml_request
    }

    response = requests.get(url, params=params, headers={'Authorization': f'Bearer {access_token}'})
    
    if response.status_code != 200:
        logger.error(f"Failed to get shipping rates: {response.status_code} {response.text}")
        return response.status_code, response.text

    # Parse the XML response to extract the base rate
    root = ET.fromstring(response.text)
    rate = root.find('.//Postage/Rate').text
    return response.status_code, rate

@api.route('/get_token')
class GetToken(Resource):
    def get(self):
        # Hardcoded consumer key and secret
        consumer_key = 'jFkNW03sWReefGWG0kjyVoM3uBATHh3G'
        consumer_secret = '3GeuEDfBikhGpsXK'

        access_token, status_code, error = get_oauth_token(consumer_key, consumer_secret)

        if not access_token:
            return {"error": "Failed to get OAuth token.", "status_code": status_code, "response": error}, status_code
        
        return {"access_token": access_token}, status_code

@api.route('/get_rates')
class GetRates(Resource):
    def get(self):
        access_token = request.args.get('access_token')
        service = request.args.get('service')
        # User-provided details
        zip_origination = request.args.get('zip_origination')
        zip_destination = request.args.get('zip_destination')
        pounds = request.args.get('pounds')
        
        # Convert pounds to ounces
        ounces = float(pounds) * 16
        
        package_details = {
            'ZipOrigination': zip_origination,
            'ZipDestination': zip_destination,
            'Pounds': pounds,
            'Ounces': ounces
        }

        status_code, rate = get_shipping_rates(access_token, '826ADISY3274', package_details, service)
        
        if status_code != 200:
            return {"error": "Failed to get shipping rates.", "status_code": status_code, "response": rate}, status_code
        
        return {"shipping_rate": rate}, status_code

# Adding the namespace to the api (assuming the API is created elsewhere)
# Example: api.add_namespace(api, path='/api/usps')
