from flask import Flask
from .extentions import api, db
from .resources import ns
from .fedex import fedex_ns
from .ups import ups_ns 
from .usps import usps_ns

def create_app():
    app = Flask(__name__)

    app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///db.sqlite'
    app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False

    api.init_app(app)
    db.init_app(app)

    api.add_namespace(ns)
    api.add_namespace(fedex_ns)
    api.add_namespace(ups_ns)
    api.add_namespace(usps_ns)

    return app

if __name__ == '__main__':
    app = create_app()
    app.run(debug=True)