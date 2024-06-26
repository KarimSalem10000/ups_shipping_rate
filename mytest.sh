#!/bin/bash

# Function to get the access token
get_access_token() {
  local response
  response=$(curl -s -X POST "http://127.0.0.1:5000/fedex/token")

  echo "$response" | grep -Po '"access_token": *\K"[^"]*"' | sed 's/"//g'
}

# Function to get rate quotes using the access token
get_rate_quote() {
  local access_token="$1"
  local payload='{
    "accountNumber": {"value": "740561073"},
    "rateRequestControlParameters": {"returnTransitTimes": true},
    "requestedShipment": {
      "shipper": {"address": {"postalCode": "94105", "countryCode": "US"}},
      "recipient": {"address": {"postalCode": "10001", "countryCode": "US"}},
      "pickupType": "DROPOFF_AT_FEDEX_LOCATION",
      "rateRequestType": ["ACCOUNT"],
      "requestedPackageLineItems": [{
        "groupPackageCount": 1,
        "weight": {"units": "LB", "value": 10},
        "dimensions": {"length": 10, "width": 10, "height": 10, "units": "IN"}
      }]
    }
  }'

  curl -s -X POST "https://apis-sandbox.fedex.com/rate/v1/rates/quotes" \
    -H "Authorization: Bearer $access_token" \
    -H "Content-Type: application/json" \
    -d "$payload"
}

# Function to parse and extract the rate quote details into a table format
parse_rate_quote() {
  local response="$1"
  echo "Service Type | Service Name | Total Net Charge"
  echo "------------------------------------------------"
  echo "$response" | grep -Po '"serviceType":"[^"]*"' | sed 's/"serviceType":"//;s/"//' | while read -r serviceType; do
    serviceName=$(echo "$response" | grep -Po '"serviceName":"[^"]*"' | sed 's/"serviceName":"//;s/"//' | head -1)
    totalNetCharge=$(echo "$response" | grep -oP '"serviceType":"'$serviceType'".*?"totalNetCharge":[^,]*' | grep -oP '"totalNetCharge":\K[^,]*')
    echo "$serviceType | $serviceName | $totalNetCharge"
  done
}

# Main script
access_token=$(get_access_token)

if [ -z "$access_token" ]; then
  echo "Failed to retrieve access token."
  exit 1
else
  echo "Access Token: $access_token"
fi

rate_quote_response=$(get_rate_quote "$access_token")
echo "Rate Quote Response: $rate_quote_response"

parse_rate_quote "$rate_quote_response"
