from flask_restx import Namespace

ups_ns = Namespace('ups', description='ups Rates and Transit Times API')

from . import routes
