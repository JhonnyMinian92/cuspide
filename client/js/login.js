(function (window, document) {
  'use strict';

  const setButtonLoading = function (button, isLoading) {
    if (!button) return;

    button.disabled = isLoading;
    button.innerHTML = isLoading
      ? '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Verificando...'
      : '<i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión';
  };

  const showLoginError = function (message) {
    const alertEl = document.getElementById('login-alert');
    const messageEl = document.getElementById('login-alert-msg');

    if (messageEl) {
      messageEl.textContent = message;
    }

    if (alertEl) {
      alertEl.classList.remove('d-none');
    }
  };

  const hideLoginError = function () {
    const alertEl = document.getElementById('login-alert');
    if (alertEl) {
      alertEl.classList.add('d-none');
    }
  };

  const togglePasswordVisibility = function () {
    const passwordInput = document.getElementById('password');
    const icon = document.getElementById('pwdIcon');
    if (!passwordInput || !icon) return;

    const isHidden = passwordInput.type === 'password';
    passwordInput.type = isHidden ? 'text' : 'password';
    icon.className = isHidden ? 'bi bi-eye-slash' : 'bi bi-eye';
  };

  const handleLogin = async function (event) {
    event.preventDefault();

    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const button = document.getElementById('btn-login');

    const username = usernameInput ? usernameInput.value.trim() : '';
    const password = passwordInput ? passwordInput.value : '';

    hideLoginError();
    setButtonLoading(button, true);

    try {
      await window.Cuspide.api.login(username, password);
      window.location.href = CONFIG.ROUTES.dashboard;
    } catch (error) {
      const message = error && error.message
        ? error.message
        : 'No se pudo iniciar sesión. Revisa la conexión e intenta nuevamente.';
      showLoginError(message);
    } finally {
      setButtonLoading(button, false);
    }
  };

  document.addEventListener('DOMContentLoaded', function () {
    if (window.Cuspide.auth.getToken()) {
      window.location.href = CONFIG.ROUTES.dashboard;
      return;
    }

    const form = document.getElementById('login-form');
    const toggleButton = document.getElementById('togglePassword');
    const closeAlertButton = document.getElementById('login-alert-close');

    if (form) {
      form.addEventListener('submit', handleLogin);
    }

    if (toggleButton) {
      toggleButton.addEventListener('click', togglePasswordVisibility);
    }

    if (closeAlertButton) {
      closeAlertButton.addEventListener('click', hideLoginError);
    }
  });

  window.handleLogin = handleLogin;
  window.togglePasswordVisibility = togglePasswordVisibility;
})(window, document);
