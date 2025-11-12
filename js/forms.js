// Conference Registration Form
const conferenceForm = document.getElementById('conferenceForm');
if (conferenceForm) {
    // Show payment details based on selection
    const paymentMethod = document.getElementById('paymentMethod');
    if (paymentMethod) {
        paymentMethod.addEventListener('change', function() {
            // Hide all payment details
            document.querySelectorAll('.payment-details').forEach(detail => {
                detail.style.display = 'none';
            });
            
            // Show selected payment details
            if (this.value === 'bank-transfer') {
                document.getElementById('bankDetails').style.display = 'block';
            } else if (this.value === 'online-payment') {
                document.getElementById('onlinePayment').style.display = 'block';
            }
        });
    }
    
    conferenceForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Simple form validation
        const fullName = document.getElementById('fullName').value;
        const email = document.getElementById('email').value;
        const phone = document.getElementById('phone').value;
        const profession = document.getElementById('profession').value;
        const paymentMethod = document.getElementById('paymentMethod').value;
        const terms = document.getElementById('terms').checked;
        
        if (fullName && email && phone && profession && paymentMethod && terms) {
            // Show loading
            const submitBtn = conferenceForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            submitBtn.disabled = true;
            
            // Prepare data
            const formData = {
                fullName: fullName,
                email: email,
                phone: phone,
                profession: profession,
                organization: document.getElementById('organization').value,
                paymentMethod: paymentMethod
            };
            
            // Send to API
            fetch('/Homeland-website/api/Registrations.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (paymentMethod === 'online-payment') {
                        alert('Registration successful! Reference: ' + data.reference + '. You will now be redirected to payment.');
                        // window.location.href = 'payment-gateway-url';
                    } else {
                        alert('Thank you for your registration! Reference: ' + data.reference + '. We will contact you shortly.');
                        this.reset();
                        document.querySelectorAll('.payment-details').forEach(detail => {
                            detail.style.display = 'none';
                        });
                    }
                } else {
                    alert('Registration failed: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Registration failed. Please try again or contact us directly.');
            })
            .finally(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            });
        } else {
            alert('Please fill in all required fields and agree to the terms.');
        }
    });
}

// MoreLife Form Handling
const morelifeForm = document.getElementById('morelifeForm');
if (morelifeForm) {
    let currentStep = 1;
    const totalSteps = 2;
    
    // Multi-select functionality for challenges
    const challengeDropdown = document.getElementById('challengeDropdown');
    const challengeOptions = document.getElementById('challengeOptions');
    const selectedChallenges = document.getElementById('selectedChallenges');
    
    if (challengeDropdown) {
        challengeDropdown.addEventListener('click', function() {
            challengeOptions.classList.toggle('active');
        });
        
        // Handle challenge selection
        document.querySelectorAll('#challengeOptions input[type="checkbox"]').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateSelectedChallenges();
            });
        });
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            if (!challengeDropdown.contains(event.target)) {
                challengeOptions.classList.remove('active');
            }
        });
    }
    
    function updateSelectedChallenges() {
        const selected = [];
        const selectedText = document.getElementById('selectedText');
        
        document.querySelectorAll('#challengeOptions input[type="checkbox"]:checked').forEach(checkbox => {
            const label = checkbox.nextElementSibling.textContent;
            selected.push(label);
        });
        
        // Update selected challenges display
        selectedChallenges.innerHTML = '';
        selected.forEach(challenge => {
            const tag = document.createElement('div');
            tag.className = 'challenge-tag';
            tag.innerHTML = `
                ${challenge}
                <i class="fas fa-times" onclick="removeChallenge('${challenge}')"></i>
            `;
            selectedChallenges.appendChild(tag);
        });
        
        // Update dropdown text
        if (selected.length > 0) {
            selectedText.textContent = selected.join(', ');
        } else {
            selectedText.textContent = 'Select one or more challenges';
        }
    }
    
    window.removeChallenge = function(challenge) {
        const checkbox = document.querySelector(`#challengeOptions input[type="checkbox"]:checked`);
        if (checkbox && checkbox.nextElementSibling.textContent === challenge) {
            checkbox.checked = false;
            updateSelectedChallenges();
        }
    };
    
    // Form navigation
    window.nextStep = function(current) {
        if (validateStep(current)) {
            document.getElementById(`step${current}`).classList.remove('active');
            document.getElementById(`step${current + 1}`).classList.add('active');
            currentStep = current + 1;
        }
    };
    
    window.prevStep = function(current) {
        document.getElementById(`step${current}`).classList.remove('active');
        document.getElementById(`step${current - 1}`).classList.add('active');
        currentStep = current - 1;
    };
    
    function validateStep(step) {
        if (step === 1) {
            const name = document.getElementById('name').value;
            const location = document.getElementById('location').value;
            const email = document.getElementById('email').value;
            const age = document.getElementById('age').value;
            const education = document.getElementById('education').value;
            
            if (!name || !location || !email || !age || !education) {
                alert('Please fill in all required fields.');
                return false;
            }
            
            const selectedChallenges = document.querySelectorAll('#challengeOptions input[type="checkbox"]:checked');
            if (selectedChallenges.length === 0) {
                alert('Please select at least one challenge area.');
                return false;
            }
            
            return true;
        }
        
        return true;
    }
    
    // Form submission
    morelifeForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        if (validateStep(currentStep)) {
            // Show loading
            const submitBtn = morelifeForm.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
            submitBtn.disabled = true;
            
            // Collect form data
            const formData = new FormData();
            formData.append('name', document.getElementById('name').value);
            formData.append('location', document.getElementById('location').value);
            formData.append('email', document.getElementById('email').value);
            formData.append('age', document.getElementById('age').value);
            formData.append('education', document.getElementById('education').value);
            
            // Get selected challenges
            const challenges = [];
            document.querySelectorAll('#challengeOptions input[type="checkbox"]:checked').forEach(checkbox => {
                challenges.push(checkbox.nextElementSibling.textContent);
            });
            formData.append('challenges', JSON.stringify(challenges));
            
            // Step 2 data
            formData.append('cause', document.getElementById('cause').value);
            formData.append('duration', document.getElementById('duration').value);
            formData.append('incident', document.getElementById('incident').value);
            formData.append('medication', document.getElementById('medication').value);
            formData.append('start_month', document.getElementById('start-month').value);
            formData.append('session_type', document.getElementById('session-type').value);
            
            // Simulate API call
            setTimeout(() => {
                // Hide form and show success message
                document.querySelectorAll('.form-step').forEach(step => {
                    step.style.display = 'none';
                });
                document.getElementById('successMessage').style.display = 'block';
                
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 2000);
        }
    });
}

// Contact Form Handling
const contactForm = document.getElementById('contactForm');
if (contactForm) {
    contactForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = contactForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
        submitBtn.disabled = true;
        
        // Simulate form submission
        setTimeout(() => {
            alert('Thank you for your message! We will get back to you soon.');
            contactForm.reset();
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 1500);
    });
}

// Newsletter Form Handling
const newsletterForm = document.getElementById('newsletterForm');
if (newsletterForm) {
    newsletterForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const emailInput = newsletterForm.querySelector('input[type="email"]');
        const submitBtn = newsletterForm.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        
        if (emailInput.value) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            submitBtn.disabled = true;
            
            setTimeout(() => {
                alert('Thank you for subscribing to our newsletter!');
                emailInput.value = '';
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 1000);
        }
    });
}