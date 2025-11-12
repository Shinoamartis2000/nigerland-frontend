// Enhanced form validation for training registration
document.addEventListener('DOMContentLoaded', function() {
    const trainingForm = document.getElementById('trainingForm');
    
    if (trainingForm) {
        // Real-time validation
        const inputs = trainingForm.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            input.addEventListener('input', validateTrainingForm);
            input.addEventListener('change', validateTrainingForm);
        });
        
        // Initial validation
        validateTrainingForm();
    }
});

function validateTrainingForm() {
    const form = document.getElementById('trainingForm');
    const submitButton = document.getElementById('submitTrainingBtn');
    
    if (!form || !submitButton) return;
    
    const requiredFields = form.querySelectorAll('[required]');
    let allValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value.trim()) {
            allValid = false;
        }
        
        // Special validation for email
        if (field.type === 'email' && field.value.trim()) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(field.value.trim())) {
                allValid = false;
            }
        }
        
        // Special validation for phone
        if (field.name === 'phone' && field.value.trim()) {
            const phoneRegex = /^[0-9+\-\s()]{10,}$/;
            if (!phoneRegex.test(field.value.trim())) {
                allValid = false;
            }
        }
    });
    
    submitButton.disabled = !allValid;
    return allValid;
}

// Enhanced form submission
async function submitTrainingForm(event) {
    event.preventDefault();
    
    if (!validateTrainingForm()) {
        showTrainingAlert('Please fill all required fields correctly.', 'error');
        return;
    }
    
    const submitButton = document.getElementById('submitTrainingBtn');
    const originalText = submitButton.innerHTML;
    
    // Show loading state
    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    submitButton.disabled = true;
    
    try {
        const formData = new FormData(document.getElementById('trainingForm'));
        const trainingData = {
            full_name: formData.get('full_name'),
            email: formData.get('email'),
            phone: formData.get('phone'),
            profession: formData.get('profession'),
            organization: formData.get('organization'),
            position: formData.get('position'),
            experience: formData.get('experience'),
            expectations: formData.get('expectations'),
            special_requirements: formData.get('special_requirements'),
            training_title: formData.get('training_title'),
            training_id: formData.get('training_id')
        };
        
        const response = await fetch('Api/submit_training_registration.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(trainingData)
        });
        
        const data = await response.json();
        
        if (data.success) {
            showTrainingAlert('Registration submitted successfully!', 'success');
            document.getElementById('trainingForm').reset();
            
            // Redirect to success page after 2 seconds
            setTimeout(() => {
                window.location.href = 'training-success.html';
            }, 2000);
            
        } else {
            throw new Error(data.message || 'Registration failed');
        }
        
    } catch (error) {
        console.error('Training registration error:', error);
        showTrainingAlert(`Registration failed: ${error.message}`, 'error');
    } finally {
        submitButton.innerHTML = originalText;
        submitButton.disabled = false;
    }
}

function showTrainingAlert(message, type = 'error') {
    // Remove existing alerts
    const existingAlert = document.querySelector('.training-alert');
    if (existingAlert) {
        existingAlert.remove();
    }
    
    const alert = document.createElement('div');
    alert.className = `training-alert alert-${type}`;
    alert.innerHTML = `
        <div class="alert-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add styles
    alert.style.cssText = `
        position: fixed;
        top: 100px;
        right: 20px;
        background: ${type === 'success' ? '#d4edda' : '#f8d7da'};
        color: ${type === 'success' ? '#155724' : '#721c24'};
        padding: 15px 20px;
        border-radius: 8px;
        border: 1px solid ${type === 'success' ? '#c3e6cb' : '#f5c6cb'};
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        max-width: 400px;
        animation: slideInRight 0.3s ease;
    `;
    
    document.body.appendChild(alert);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (alert.parentNode) {
            alert.style.animation = 'slideOutRight 0.3s ease';
            setTimeout(() => alert.remove(), 300);
        }
    }, 5000);
}