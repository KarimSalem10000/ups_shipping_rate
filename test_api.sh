#!/bin/bash

#K^^K Testing the /fedex/token endpoint
curl -X POST http://127.0.0.1:5000/fedex/token

# Testing the /fedex/rate-quote endpoint
curl -X POST http://127.0.0.1:5000/fedex/rate-quote \
-H "Content-Type: application/json" \
-d '{
  "service_type": "FEDEX_GROUND",
  "shipper": {
    "postalCode": "94105",
    "countryCode": "US"
  },
  "recipient": {
    "postalCode": "10001",
    "countryCode": "US"
  },
  "pickupType": "DROPOFF_AT_FEDEX_LOCATION",
  "requestedPackageLineItems": [{
    "groupPackageCount": 1,
    "weight": {
      "units": "LB",
      "value": 10
    },
    "dimensions": {
      "length": 10,
      "width": 10,
      "height": 10,
      "units": "IN"
    }
  }]
}'
