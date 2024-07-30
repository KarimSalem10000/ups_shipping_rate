import requests
import json

# USPS API credentials
CONSUMER_KEY = "05lIwUQ9Ho07ZzezVNLMirLWtjPm4CKY"
CONSUMER_SECRET = "zDnAVGWOH95AkXSP"
ACCOUNT_NUMBER = "95175772"
TOKEN_URL = "https://api.usps.com/oauth/token"
BASE_RATES_URL = "https://api.usps.com/prices/v3/base-rates/search"
EXTRA_SERVICE_RATES_URL = "https://api.usps.com/prices/v3/extra-service-rates/search"

# Function to get OAuth token
def get_token():
    response = requests.post(
        TOKEN_URL,
        auth=(CONSUMER_KEY, CONSUMER_SECRET),
        headers={"Content-Type": "application/x-www-form-urlencoded"},
        data="grant_type=client_credentials"
    )
    response_data = response.json()
    return response_data["access_token"]

# Function to call base rates API
def get_base_rates(token):
    headers = {
        "Content-Type": "application/json",
        "Authorization": f"Bearer {token}"
    }
    data = {
        "originZIPCode": "78664",
        "destinationZIPCode": "78665",
        "weight": 7,
        "length": 9,
        "width": 0.25,
        "height": 6,
        "mailClass": "PARCEL_SELECT",
        "processingCategory": "MACHINABLE",
        "destinationEntryFacilityType": "NONE",
        "rateIndicator": "DR",
        "priceType": "COMMERCIAL",
        "accountType": "EPS",
        "accountNumber": ACCOUNT_NUMBER,
        "mailingDate": "2023-05-25"
    }
    response = requests.post(BASE_RATES_URL, headers=headers, json=data)
    return response.json()

# Function to call extra services rates API
def get_extra_service_rates(token):
    headers = {
        "Content-Type": "application/json",
        "Authorization": f"Bearer {token}"
    }
    data = {
        "extraServices": 415,
        "mailClass": "PARCEL_SELECT",
        "priceType": "RETAIL",
        "itemValue": 0,
        "weight": 5,
        "mailingDate": "2023-05-25",
        "accountType": "EPS",
        "accountNumber": ACCOUNT_NUMBER
    }
    response = requests.post(EXTRA_SERVICE_RATES_URL, headers=headers, json=data)
    return response.json()

def main():
    print("Getting OAuth token...")
    token = get_token()
    print("Token:", token)

    print("Calling Base Rates API...")
    base_rates_response = get_base_rates(token)
    print("Base Rates Response:")
    print(json.dumps(base_rates_response, indent=2))

    print("Calling Extra Services Rates API...")
    extra_service_rates_response = get_extra_service_rates(token)
    print("Extra Service Rates Response:")
    print(json.dumps(extra_service_rates_response, indent=2))

if __name__ == "__main__":
    main()
