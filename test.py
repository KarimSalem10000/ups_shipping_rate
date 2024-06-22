import requests
import os
import requests

url = "https://wwwcie.ups.com/security/v1/oauth/authorize"

query = {
  "client_id": "string",
  "redirect_uri": "string",
  "response_type": "string",
  "state": "string",
  "scope": "string"
}

response = requests.get(url, params=query)

data = response.json()
print(data)

client_id = os.getenv('UPS_CLIENT_ID')
client_secret = os.getenv('UPS_CLIENT_SECRET')
url = "https://wwwcie.ups.com/security/v1/oauth/refresh"

payload = {
  "grant_type": "refresh_token",
  "refresh_token": "string"
}

headers = {"Content-Type": "application/x-www-form-urlencoded"}

response = requests.post(url, data=payload, headers=headers, auth=(client_id,client_secret))

data = response.json()
print(data)