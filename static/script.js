document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('subject-form');
    const resultDiv = document.getElementById('result');
    const subjectLineP = document.getElementById('subject-line');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const customerName = document.getElementById('customer-name').value;
        const painPoint = document.getElementById('pain-point').value;

        const response = await fetch('/api/generate_subject', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                customer_name: customerName,
                pain_point: painPoint
            }),
        });

        const data = await response.json();

        if (data.subject) {
            subjectLineP.textContent = data.subject;
            resultDiv.classList.remove('hidden');
        }
    });
});
