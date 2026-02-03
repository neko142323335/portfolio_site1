function switchTab(tab) {
      document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
      document.querySelectorAll('.form').forEach(f => f.classList.add('hidden'));
      document.getElementById(tab + '-tab').classList.add('active');
      document.getElementById(tab + '-form').classList.remove('hidden');
    }
    document.getElementById('login-tab').addEventListener('click', () => switchTab('login'));
    document.getElementById('register-tab').addEventListener('click', () => switchTab('register'));
    function togglePassword(id) {
      const input = document.getElementById(id);
      const eye = input.nextElementSibling.querySelector('.eye');
      if (input.type === 'password') {
        input.type = 'text';
        eye.innerHTML = '<path d="M2.999 3L21 21M9.5 9.5a3.5 3.5 0 004.95 4.95M12 6c-1.5 0-2.8.6-3.8 1.6L9.5 9.5M12 6c1.5 0 2.8.6 3.8 1.6L14.5 9.5M12 6v3M12 18v-3M12 18c-1.5 0-2.8-.6-3.8-1.6L9.5 14.5M12 18c1.5 0 2.8-.6 3.8-1.6L14.5 14.5"/>';
      } else {
        input.type = 'password';
        eye.innerHTML = '<path d="M12 4.5C7 4.5 2.73 7.61 1 12C2.73 16.39 7 19.5 12 19.5C17 19.5 21.27 16.39 23 12C21.27 7.61 17 4.5 12 4.5ZM12 17C9.24 17 7 14.76 7 12S9.24 7 7 12 14.76 17 12 17ZM12 9C13.66 9 15 10.34 15 12S13.66 15 12 15 9 13.66 9 12 10.34 9 12 9Z"/>';
      }
    }