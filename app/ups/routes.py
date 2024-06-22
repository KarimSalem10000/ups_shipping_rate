import os
import base64
import requests
from flask import request, jsonify
from flask_restx import Resource
from . import ups_ns


@ups_ns.route('/token')
class Token(Resource):
    def get(self):
        # Set your application credentials
        client_id = 'IPfM41sqLVodA9NfSUHZAAJ8AoGXR2XgShpgXjrsLqAwGJju'
        client_secret = '81o4lr2eDS2ytW0QushIXmID2CxEn9PPf5i0oH5P3S6tL2G8kafbUG9ll5C6ItxF'
        
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
        response = requests.post('https://wwwcie.ups.com/security/v1/oauth/token', data=payload, headers=headers)
        
        # Handle response
        if response.status_code == 200:
            response_data = response.json()
            access_token = response_data.get('access_token')
            refresh_token = response_data.get('refresh_token')
            
            # Save the tokens to a file
            folder_path = os.path.join(os.path.dirname(__file__), 'tokens')
            if not os.path.exists(folder_path):
                os.makedirs(folder_path)
            
            file_path = os.path.join(folder_path, 'usersTokens.json')
            with open(file_path, 'w') as token_file:
                token_file.write(response.text)
            
            return jsonify({
                'message': 'Tokens retrieved and saved successfully',
                'access_token': access_token,
                'refresh_token': refresh_token,
                'file_path': file_path
            })
        else:
            return jsonify({
                'message': 'Failed to retrieve tokens',
                'error': response.text
            }), response.status_code

@ups_ns.route('/shipping-cost')
class ShippingCost(Resource):
    def post(self):
        client_id = 'IPfM41sqLVodA9NfSUHZAAJ8AoGXR2XgShpgXjrsLqAwGJju'
        client_secret = '81o4lr2eDS2ytW0QushIXmID2CxEn9PPf5i0oH5P3S6tL2G8kafbUG9ll5C6ItxF'
        ups_account_number = 'J0469H'

        def get_token(client_id, client_secret):
            credentials = base64.b64encode(f"{client_id}:{client_secret}".encode('utf-8')).decode('utf-8')
            payload = {'grant_type': 'client_credentials'}
            headers = {
                'Content-Type': 'application/x-www-form-urlencoded',
                'Authorization': f'Basic {credentials}'
            }
            
            response = requests.post('https://wwwcie.ups.com/security/v1/oauth/token', data=payload, headers=headers)
            
            if response.status_code == 200:
                response_data = response.json()
                return response_data.get('access_token')
            else:
                return None

        def get_shipping_cost(access_token, shipper_info, to_address_info, from_address_info, package_info):
            url = "https://onlinetools.ups.com/api/rating/v1/rate"
            headers = {
                "Authorization": f"Bearer {access_token}",
                "Content-Type": "application/json"
            }
            payload = {
                "RateRequest": {
                    "Request": {
                        "TransactionReference": {
                            "CustomerContext": "CustomerContext",
                            "TransactionIdentifier": "TransactionIdentifier"
                        }
                    },
                    "Shipment": {
                        "Shipper": {
                            "Name": shipper_info['name'],
                            "ShipperNumber": shipper_info['account_number'],
                            "Address": {
                                "AddressLine": [
                                    shipper_info['address1'],
                                    shipper_info['address2'],
                                    shipper_info['address3']
                                ],
                                "City": shipper_info['city'],
                                "StateProvinceCode": shipper_info['state'],
                                "PostalCode": shipper_info['zip'],
                                "CountryCode": shipper_info['country']
                            }
                        },
                        "ShipTo": {
                            "Name": to_address_info['name'],
                            "Address": {
                                "AddressLine": [
                                    to_address_info['address1'],
                                    to_address_info['address2'],
                                    to_address_info['address3']
                                ],
                                "City": to_address_info['city'],
                                "StateProvinceCode": to_address_info['state'],
                                "PostalCode": to_address_info['zip'],
                                "CountryCode": to_address_info['country']
                            }
                        },
                        "ShipFrom": {
                            "Name": from_address_info['name'],
                            "Address": {
                                "AddressLine": [
                                    from_address_info['address1'],
                                    from_address_info['address2'],
                                    from_address_info['address3']
                                ],
                                "City": from_address_info['city'],
                                "StateProvinceCode": from_address_info['state'],
                                "PostalCode": from_address_info['zip'],
                                "CountryCode": from_address_info['country']
                            }
                        },
                        "PaymentDetails": {
                            "ShipmentCharge": {
                                "Type": "01",
                                "BillShipper": {
                                    "AccountNumber": shipper_info['account_number']
                                }
                            }
                        },
                        "Service": {
                            "Code": package_info['service'],
                            "Description": "ground"
                        },
                        "NumOfPieces": "1",
                        "Package": {
                            "PackagingType": {
                                "Code": package_info['package_type'],
                                "Description": "Packaging"
                            },
                            "Dimensions": {
                                "UnitOfMeasurement": {
                                    "Code": "IN",
                                    "Description": "Inches"
                                },
                                "Length": package_info['length'],
                                "Width": package_info['width'],
                                "Height": package_info['height']
                            },
                            "PackageWeight": {
                                "UnitOfMeasurement": {
                                    "Code": "LBS",
                                    "Description": "Pounds"
                                },
                                "Weight": package_info['Weight']
                            }
                        }
                    }
                }
            }
            response = requests.post(url, headers=headers, json=payload)
            response_data = response.json()
            
            print("Response Data:", response_data)
            
            try:
                total_charges = response_data['RateResponse']['RatedShipment']['TotalCharges']['MonetaryValue']
                return total_charges
            except KeyError as e:
                print(f"KeyError: {e}")
                return {'error': f"KeyError: {e}, Response Data: {response_data}"}, 500

        shipper_info = {
            'account_number': ups_account_number,
            'name': 'Mr. President',
            'address1': '1600 Pennsylvania Avenue NW',
            'address2': '',
            'address3': '',
            'city': 'Washington',
            'state': 'DC',
            'zip': '20500',
            'country': 'US',
        }

        from_address_info = {
            'name': 'Mr. President',
            'address1': '1600 Pennsylvania Avenue NW',
            'address2': '',
            'address3': '',
            'city': 'Washington',
            'state': 'DC',
            'zip': '20500',
            'country': 'US',
        }

        to_address_info = {
            'name': 'Thomas Jefferson',
            'address1': '931 Thomas Jefferson Parkway',
            'address2': '',
            'address3': '',
            'city': 'Charlottesville',
            'state': 'VA',
            'zip': '22902',
            'country': 'US',
        }

        package_info = {
            'service': '03',
            'package_type': '02',
            'Weight': '50',
            'length': '7',
            'width': '4',
            'height': '2',
        }

        access_token = get_token(client_id, client_secret)

        if access_token is None:
            return jsonify({'error': 'Failed to retrieve access token'}), 500

        total_charges = get_shipping_cost(access_token, shipper_info, to_address_info, from_address_info, package_info)

        if isinstance(total_charges, dict) and 'error' in total_charges:
            return total_charges, 500
        
        return jsonify({'access token': access_token, 'total_charges': total_charges})