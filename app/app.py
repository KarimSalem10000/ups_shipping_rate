from flask import Flask
from flask_restx import Api
from flask_cors import CORS
from . import fedex_ns

app = Flask(__name__)
CORS(app)  # This will enable CORS for all routes
api = Api(app)
api.add_namespace(fedex_ns)

if __name__ == "__main__":
    app.run(debug=True)
