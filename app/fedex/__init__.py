from flask_restx import Namespace

fedex_ns = Namespace('fedex', description='FedEx Rates and Transit Times API')

from . import routes
