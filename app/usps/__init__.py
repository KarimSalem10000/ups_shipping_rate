from flask_restx import Namespace

usps_ns = Namespace('usps', description='usps Rates and Transit Times API')

from . import routes
