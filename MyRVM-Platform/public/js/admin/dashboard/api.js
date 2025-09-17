// API Functions

async function fetchRvmData() {
    try {
        const response = await fetch(`${config.apiBaseUrl}/rvm/monitoring`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error fetching RVM data:', error);
        throw error;
    }
}

async function updateRvmStatus(rvmId, newStatus) {
    try {
        const response = await fetch(`${config.apiBaseUrl}/rvm/${rvmId}/status`, {
            method: 'PUT',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken
            },
            body: JSON.stringify({
                status: newStatus,
                timestamp: new Date().toISOString()
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error updating RVM status:', error);
        throw error;
    }
}

async function fetchProcessingEngines() {
    try {
        const response = await fetch(`${config.apiBaseUrl}/processing-engines`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error fetching processing engines:', error);
        throw error;
    }
}

async function fetchRvmDataForEngines() {
    try {
        const response = await fetch(`${config.apiBaseUrl}/rvm`, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error fetching RVM data for engines:', error);
        throw error;
    }
}

// Health Check Functions
async function pingProcessingEngine(engineId) {
    try {
        const response = await fetch(`${config.apiBaseUrl}/processing-engines/${engineId}/ping`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error pinging processing engine:', error);
        throw error;
    }
}

async function pingAllEngines() {
    try {
        const response = await fetch(`${config.apiBaseUrl}/processing-engines/ping-all`, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': config.csrfToken
            }
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const data = await response.json();
        return data;
    } catch (error) {
        console.error('Error pinging all engines:', error);
        throw error;
    }
}
