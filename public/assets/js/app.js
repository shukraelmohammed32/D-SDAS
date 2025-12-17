// public/assets/js/app.js

document.addEventListener('DOMContentLoaded', () => {
    const deptSelect = document.getElementById('deptSelect');
    const lockBtn = document.getElementById('lockBtn');
    
    // Prediction Elements
    const predictionWidget = document.getElementById('predictionWidget');
    const light = document.getElementById('light-display');
    const predTitle = document.getElementById('predTitle');
    const predMsg = document.getElementById('predMsg');

    // 1. HANDLE DEPARTMENT CHANGE (Get Prediction)
    deptSelect.addEventListener('change', async (e) => {
        const deptId = e.target.value;
        
        // UI Reset
        predictionWidget.classList.remove('hidden');
        light.className = 'traffic-light gray'; // Reset to gray/loading
        predTitle.innerText = "Checking Availability...";
        predMsg.innerText = "Retrieving real-time capacity and rank data...";

        try {
            // CALL API
            const response = await fetch(`api/predict.php?dept_id=${deptId}`);
            const data = await response.json();

            // UPDATE UI
            light.className = `traffic-light ${data.color}`;
            
            if (data.color === 'green') predTitle.innerText = "Excellent Chance";
            else if (data.color === 'yellow') predTitle.innerText = "Moderate Risk";
            else predTitle.innerText = "High Risk";

            predMsg.innerText = data.msg;

        } catch (error) {
            console.error(error);
            predTitle.innerText = "System Error";
            predMsg.innerText = "Could not contact prediction engine.";
        }
    });

    // 2. HANDLE LOCK CHOICE
    lockBtn.addEventListener('click', async () => {
        const deptId = deptSelect.value;
        if (!deptId) {
            alert("Please select a department first.");
            return;
        }

        if(!confirm("Are you sure? This will lock your choice for 30 minutes.")) return;

        lockBtn.innerText = "Processing...";
        lockBtn.disabled = true;

        try {
            const response = await fetch('api/lock.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ dept_id: deptId })
            });
            const result = await response.json();

            alert(result.msg);

            if (result.success) {
                lockBtn.innerText = "Choice Locked";
                // Optionally reload page to show locked state permanently
            } else {
                lockBtn.innerText = "Lock Choice";
                lockBtn.disabled = false;
            }

        } catch (error) {
            alert("Submission failed.");
            lockBtn.disabled = false;
        }
    });
});