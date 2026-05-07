const btnVer = document.getElementById('btnVer');

const passwordInput = document.getElementById('passwordInput');

if (btnVer && passwordInput) {

    btnVer.addEventListener('click', () => {

        if (passwordInput.type === 'password') {

            passwordInput.type = 'text';

            btnVer.textContent = 'Ocultar';

        } else {

            passwordInput.type = 'password';

            btnVer.textContent = 'Ver';
        }
    });
}