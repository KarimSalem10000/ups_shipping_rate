#!/bin/bash

CLIENT_ID="l7cd15a6eddf8b4b13b827f12d3b7e8b50"
SECRET_ID="45069dacbfa94424a5ad592b8f4ecbb8"

response=$(curl -s -X POST \
  https://apis-sandbox.fedex.com/oauth/token \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=client_credentials&client_id=${CLIENT_ID}&client_secret=${SECRET_ID}")

echo "Response from FedEx:"
echo "${response}"


