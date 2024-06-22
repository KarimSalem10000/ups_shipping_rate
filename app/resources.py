from flask_restx import Namespace, Resource

ns = Namespace('resources', description='API resources')

@ns.route('/hello')
class HelloWorld(Resource):
    def get(self):
        return {'hello': 'world'}