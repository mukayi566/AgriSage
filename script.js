// Modal controls
const loginBtn = document.getElementById('loginBtn');
const signupBtn = document.getElementById('signupBtn');
const loginModal = document.getElementById('loginModal');
const signupModal = document.getElementById('signupModal');
const closeLogin = document.getElementById('closeLogin');
const closeSignup = document.getElementById('closeSignup');

// Open modals
loginBtn.onclick = () => loginModal.style.display = "flex";
signupBtn.onclick = () => signupModal.style.display = "flex";

// Close modals
closeLogin.onclick = () => loginModal.style.display = "none";
closeSignup.onclick = () => signupModal.style.display = "none";

// Close if click outside
window.onclick = (e) => {
  if (e.target == loginModal) loginModal.style.display = "none";
  if (e.target == signupModal) signupModal.style.display = "none";
};

// Form handling
document.getElementById('loginForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = {
        email: formData.get('email'),
        password: formData.get('password')
    };

    fetch('login.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        const messageDiv = document.getElementById('loginMessage');
        if (data.success) {
            messageDiv.style.color = 'green';
            messageDiv.textContent = data.message;
            setTimeout(() => {
                loginModal.style.display = 'none';
                // Redirect or update UI
                window.location.reload();
            }, 1000);
        } else {
            messageDiv.style.color = 'red';
            messageDiv.textContent = data.message;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('loginMessage').textContent = 'An error occurred';
    });
});

document.getElementById('signupForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const data = {
        name: formData.get('name'),
        email: formData.get('email'),
        password: formData.get('password')
    };

    fetch('signup.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        const messageDiv = document.getElementById('signupMessage');
        if (data.success) {
            messageDiv.style.color = 'green';
            messageDiv.textContent = data.message;
            setTimeout(() => {
                signupModal.style.display = 'none';
                window.location.reload();
            }, 1000);
        } else {
            messageDiv.style.color = 'red';
            messageDiv.textContent = data.message;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        document.getElementById('signupMessage').textContent = 'An error occurred';
    });
});