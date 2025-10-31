// File: assets/js/charts.js
// Handles fetching data and rendering the Chart.js graph on the Admin Dashboard.

// Function to fetch data and update the chart
function updateLiveChart() {
    // Note: We use the API endpoint ../api/results_data.php
    fetch('../api/results_data.php?election_id=1') 
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(apiData => {
            const candidates = apiData.candidates;
            const totalVotes = apiData.total_votes;

            const labels = candidates.map(item => item.label);
            const votes = candidates.map(item => item.data);

            // Update the total votes count displayed on the dashboard
            const totalVotesElement = document.getElementById('totalVotes');
            if (totalVotesElement) {
                totalVotesElement.textContent = totalVotes;
            }
            
            const chartData = {
                labels: labels,
                datasets: [{
                    label: 'Total Votes',
                    data: votes,
                    backgroundColor: [ 
                        'rgba(255, 99, 132, 0.7)',  // Red
                        'rgba(54, 162, 235, 0.7)',  // Blue
                        'rgba(255, 206, 86, 0.7)',  // Yellow
                        'rgba(75, 192, 192, 0.7)',  // Green
                        'rgba(153, 102, 255, 0.7)', // Purple
                    ],
                    borderColor: 'rgba(0, 0, 0, 0.6)',
                    borderWidth: 1
                }]
            };

            const ctx = document.getElementById('liveVotesChart');
            if (!ctx) return;
            
            // CRITICAL: Destroy previous chart instance before redrawing
            if (window.liveChart instanceof Chart) {
                window.liveChart.destroy();
            }

            // Create new Chart instance
            window.liveChart = new Chart(ctx, {
                type: 'bar',
                data: chartData,
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 } 
                        }
                    },
                    plugins: {
                        title: {
                            display: true,
                            text: 'Live Election Vote Tally'
                        },
                        legend: {
                            display: false
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error fetching chart data:', error);
            const totalVotesElement = document.getElementById('totalVotes');
            if (totalVotesElement) {
                 totalVotesElement.textContent = 'Data error.';
            }
        });
}

// Run immediately when the page loads
window.onload = updateLiveChart;

// Set interval to update the chart every 5 seconds (5000 milliseconds)
setInterval(updateLiveChart, 5000);