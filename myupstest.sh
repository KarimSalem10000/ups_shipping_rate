#!/bin/bash

# Function to get the access token
get_access_token() {
  local response
  response=$(curl -s -X GET "http://127.0.0.1:5000/ups/token")

  echo "$response" | grep -Po '"access_token": *\K"[^"]*"' | sed 's/"//g'
}

# Function to get shipping cost using the access token
get_shipping_cost() {
  local access_token="$1"
  local payload='{
    "shipper_info": {
      "account_number": "J0469H",
      "name": "Mr. President",
      "address1": "1600 Pennsylvania Avenue NW",
      "address2": "",
      "address3": "",
      "city": "Washington",
      "state": "DC",
      "zip": "20500",
      "country": "US"
    },
    "from_address_info": {
      "name": "Mr. President",
      "address1": "1600 Pennsylvania Avenue NW",
      "address2": "",
      "address3": "",
      "city": "Washington",
      "state": "DC",
      "zip": "20500",
      "country": "US"
    },
    "to_address_info": {
      "name": "Thomas Jefferson",
      "address1": "931 Thomas Jefferson Parkway",
      "address2": "",
      "address3": "",
      "city": "Charlottesville",
      "state": "VA",
      "zip": "22902",
      "country": "US"
    },
    "package_info": {
      "service": "03",
      "package_type": "02",
      "Weight": "50",
      "length": "7",
      "width": "4",
      "height": "2"
    }
  }'

  curl -s -X POST "http://127.0.0.1:5000/ups/shipping-cost" \
    -H "Authorization: Bearer $access_token" \
    -H "Content-Type: application/json" \
    -d "$payload"
}

# Function to parse and extract the shipping cost details into a table format
parse_shipping_cost() {
  local response="$1"
  local service_descriptions=(
    ["01"]="UPS Next Day Air"
    ["02"]="UPS 2nd Day Air"
    ["03"]="UPS Ground"
    ["12"]="UPS 3 Day Select"
    ["13"]="UPS Next Day Air Saver"
    ["14"]="UPS Next Day Air Early"
    ["59"]="UPS 2nd Day Air A.M."
  )

  echo "Service Code | Service Description         | Total Charges"
  echo "-------------------------------------------------------------"
  echo "$response" | grep -Po '"\d{2}": *"[^"]*"' | while read -r line; do
    service_code=$(echo "$line" | grep -Po '^\d{2}')
    total_charges=$(echo "$line" | grep -Po ': *\K"[^"]*"' | sed 's/"//g')
    service_description="${service_descriptions[$service_code]}"
    printf "%-12s | %-25s | %s\n" "$service_code" "$service_description" "$total_charges"
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

shipping_cost_response=$(get_shipping_cost "$access_token")
echo "Shipping Cost Response: $shipping_cost_response"

parse_shipping_cost "$shipping_cost_response"
