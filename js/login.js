   let hidePasswordTimeout = null;

      function togglePassword() {
        const passwordField = document.getElementById("password");
        const eyeIcon = document.querySelector(".eye-icon");

        if (hidePasswordTimeout) {
          clearTimeout(hidePasswordTimeout);
          hidePasswordTimeout = null;
        }

        if (passwordField.type === "password") {
          passwordField.type = "text";
          eyeIcon.src = "../assets/images/eye.png";

          hidePasswordTimeout = setTimeout(() => {
            passwordField.type = "password";
            eyeIcon.src = "../assets/images/eye-off.png";
            hidePasswordTimeout = null;
          }, 5000);

        } else {
          passwordField.type = "password";
          eyeIcon.src = "../assets/images/eye-off.png";
        }
      }
document.addEventListener('DOMContentLoaded', function() {
  const form = document.querySelector('form');
  const emailInput = document.getElementById('email');
  const passwordInput = document.getElementById('password');
  const submitBtn = form.querySelector('button[type="submit"]');
  
  setupInputAnimations();
  
  form.addEventListener('submit', handleFormSubmit);
  
  emailInput.addEventListener('blur', validateEmail);
  passwordInput.addEventListener('blur', validatePassword);
  
  emailInput.addEventListener('invalid', function(e) {
    e.preventDefault();
    shakeElement(this);
  });
  
  passwordInput.addEventListener('invalid', function(e) {
    e.preventDefault();
    shakeElement(this);
  });
});

function setupInputAnimations() {
  const inputs = document.querySelectorAll('input');
  
  inputs.forEach(input => {
    input.addEventListener('focus', function() {
      this.parentElement.classList.add('focused');
      animateInput(this, 'focus');
    });
    
    input.addEventListener('blur', function() {
      this.parentElement.classList.remove('focused');
      if (!this.value) {
        animateInput(this, 'blur');
      }
    });
    
    input.addEventListener('input', function() {
      if (this.value) {
        this.classList.add('has-value');
      } else {
        this.classList.remove('has-value');
      }
    });
  });
}

function animateInput(input, type) {
  if (type === 'focus') {
    input.style.transform = 'scale(1.02)';
    setTimeout(() => {
      input.style.transform = 'scale(1)';
    }, 150);
  }
}

function validateEmail() {
  const email = this.value.trim();
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  
  if (email && !emailRegex.test(email)) {
    this.setCustomValidity('Por favor ingresa un correo electrónico válido');
    shakeElement(this);
    return false;
  } else {
    this.setCustomValidity('');
    return true;
  }
}

function validatePassword() {
  const password = this.value;
  
  if (password && password.length < 8) {
    this.setCustomValidity('La contraseña debe tener al menos 8 caracteres');
    shakeElement(this);
    return false;
  } else {
    this.setCustomValidity('');
    return true;
  }
}

function handleFormSubmit(e) {
  const submitBtn = this.querySelector('button[type="submit"]');
  const formGroups = this.querySelectorAll('.form-group');
  let isValid = true;
  
  formGroups.forEach(group => {
    const input = group.querySelector('input');
    if (input && !input.checkValidity()) {
      isValid = false;
      shakeElement(input);
    }
  });
  
  if (!isValid) {
    e.preventDefault();
    return false;
  }
  
  submitBtn.disabled = true;
  submitBtn.style.opacity = '0.7';
  const originalText = submitBtn.textContent;
  submitBtn.textContent = 'Iniciando sesión...';
  
  submitBtn.style.animation = 'pulse 1.5s infinite';
}

function shakeElement(element) {
  element.classList.add('shake');
  setTimeout(() => {
    element.classList.remove('shake');
  }, 500);
}

console.log('Login.js loaded');
