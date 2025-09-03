from flask import Flask, render_template, request, jsonify

app = Flask(__name__)

@app.route('/')
def index():
    return render_template('index.html')

@app.route('/api/generate_subject', methods=['POST'])
def generate_subject():
    data = request.get_json()
    customer_name = data.get('customer_name', 'Valued Customer')
    pain_point = data.get('pain_point', 'a challenge')

    # Simple placeholder for subject line generation
    subject = f"A solution for {pain_point} for {customer_name}"

    return jsonify({'subject': subject})

if __name__ == '__main__':
    app.run(debug=True)
