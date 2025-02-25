let registerName = "";
let registerEmail = ""; 
let registerPassword = "";
let registerPhone = "";
let registerUserType = ""; // Armazena o tipo de usuário (Freelancer ou Empresa)

// Máscara de telefone
document.getElementById('registerPhone').addEventListener('input', function(event) {
    let phone = event.target.value;
    phone = phone.replace(/\D/g, ''); // Remove caracteres não numéricos

    // Aplica a máscara (XX) XXXXX-XXXX
    if (phone.length <= 2) {
        phone = phone.replace(/^(\d{0,2})/, '($1');
    } else if (phone.length <= 7) {
        phone = phone.replace(/^(\d{0,2})(\d{0,5})/, '($1) $2');
    } else {
        phone = phone.replace(/^(\d{0,2})(\d{0,5})(\d{0,4})/, '($1) $2-$3');
    }
    event.target.value = phone;
});

// Máscara de CNPJ
document.getElementById('companyCNPJ').addEventListener('input', function(event) {
    let cnpj = event.target.value;
    cnpj = cnpj.replace(/\D/g, ''); // Remove caracteres não numéricos

    // Aplica a máscara XX.XXX.XXX/XXXX-XX
    if (cnpj.length <= 2) {
        cnpj = cnpj.replace(/^(\d{0,2})/, '$1');
    } else if (cnpj.length <= 5) {
        cnpj = cnpj.replace(/^(\d{2})(\d{0,3})/, '$1.$2');
    } else if (cnpj.length <= 8) {
        cnpj = cnpj.replace(/^(\d{2})(\d{3})(\d{0,3})/, '$1.$2.$3');
    } else if (cnpj.length <= 12) {
        cnpj = cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{0,4})/, '$1.$2.$3/$4');
    } else {
        cnpj = cnpj.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{0,2})/, '$1.$2.$3/$4-$5');
    }
    event.target.value = cnpj;
});

// Alternar os campos conforme o tipo de usuário selecionado
document.getElementById('user-type').addEventListener('change', function() {
    const userType = this.value;

    // Seleciona os campos do formulário
    const phoneField = document.getElementById('phoneField');
    const empresaFields = document.getElementById('empresaFields');

    // Lógica para mostrar/ocultar os campos de acordo com o tipo de usuário
    if (userType === 'empresa') {
        empresaFields.style.display = 'block';  // Exibe os campos de empresa
        phoneField.style.display = 'none';      // Oculta o campo de telefone

        // Limpa e oculta o campo de telefone
        document.getElementById('registerPhone').value = '';  // Limpa o valor se houver
        phoneField.innerHTML = '';  // Remove o campo de telefone completamente
    } else if (userType === 'freelancer') {
        empresaFields.style.display = 'none';   // Oculta os campos de empresa
        phoneField.style.display = 'block';     // Exibe o campo de telefone

        // Recria o campo de telefone
        let phoneInput = document.createElement('input');
        phoneInput.type = 'tel';
        phoneInput.classList.add('registerPhone');
        phoneInput.id = 'registerPhone';
        phoneInput.placeholder = '(XX) XXXXX-XXXX';
        phoneInput.required = true;
        phoneField.appendChild(phoneInput);

        // Aplica a máscara de telefone ao novo campo
        phoneInput.addEventListener('input', function(event) {
            let phone = event.target.value;
            phone = phone.replace(/\D/g, ''); // Remove caracteres não numéricos

            // Aplica a máscara (XX) XXXXX-XXXX
            if (phone.length <= 2) {
                phone = phone.replace(/^(\d{0,2})/, '($1');
            } else if (phone.length <= 7) {
                phone = phone.replace(/^(\d{0,2})(\d{0,5})/, '($1) $2');
            } else {
                phone = phone.replace(/^(\d{0,2})(\d{0,5})(\d{0,4})/, '($1) $2-$3');
            }
            event.target.value = phone;
        });
    }
});

// Função de cadastro
function register(event) {
    event.preventDefault();

    // Coleta os dados dos inputs comuns e específicos
    registerName = document.getElementById("registerName").value;
    registerEmail = document.getElementById("registerEmail").value; 
    registerPassword = document.getElementById("registerPassword").value;
    registerPhone = document.getElementById("registerPhone") ? document.getElementById("registerPhone").value : ''; // Verifica se o campo existe
    registerUserType = document.getElementById("user-type").value;

    // Validação para freelancer (telefone deve ter 11 dígitos)
    if (registerUserType === 'freelancer' && registerPhone.replace(/\D/g, '').length !== 11) {
        alert("Insira seu número completo.");
        return;
    }

    // Validação de senha
    const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{9,}$/;
    if (!passwordPattern.test(registerPassword)) {
        alert("A senha deve conter pelo menos 9 caracteres, uma letra maiúscula, uma letra minúscula, um número e um caractere especial.");
        return;
    }

    // Validação do formato de email
    const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    if (!emailPattern.test(registerEmail)) {
        alert("Por favor, insira um email válido.");
        return;
    }

    // Validação de campos obrigatórios para freelancer
    if (registerUserType === "freelancer" && !(registerName && registerEmail && registerPassword && registerPhone)) {
        alert("Por favor, insira todos os dados para o cadastro.");
        return;
    }

    // Validação de campos obrigatórios para empresa
    if (registerUserType === "empresa") {
        const companyName = document.getElementById('companyName').value;
        const companyCNPJ = document.getElementById('companyCNPJ').value;
        
        if (!(companyName && companyCNPJ)) {
            alert("Nome da empresa e CNPJ são obrigatórios.");
            return;
        }

        // Se o tipo for empresa, o telefone não é obrigatório
        if (!registerName || !registerEmail || !registerPassword) {
            alert("Por favor, insira todos os dados para o cadastro.");
            return;
        }
    }

    // Cria o objeto FormData para envio via AJAX
    const formData = new FormData();
    formData.append('name', registerName);
    formData.append('email', registerEmail);
    formData.append('password', registerPassword);
    formData.append('phone', registerPhone);
    formData.append('user_type', registerUserType);

    // Envio dos dados para register.php
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'register.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                let response = JSON.parse(xhr.responseText);
                if (response.status === "success") {
                    alert(`Conta criada com sucesso, ${registerName}! Agora faça login.`);
                    document.getElementById("container").classList.remove("active");
                } else if (response.status === "error") {
                    alert(response.message);
                }
            } catch (e) {
                console.error("Erro ao analisar JSON: " + e.message);
                alert("Erro ao processar a resposta do servidor. Tente novamente.");
            }
        } else {
            alert("Erro ao criar a conta. Tente novamente.");
            console.log(xhr.responseText);
        }
    };
    xhr.onerror = function() {
        alert("Erro de comunicação com o servidor. Tente novamente.");
    };
    xhr.send(formData);
}


// Função de login
function login(event) {
    event.preventDefault();

    const loginEmail = document.getElementById("loginEmail").value;
    const loginPassword = document.getElementById("loginPassword").value;

    const formData = new FormData();
    formData.append('email', loginEmail);
    formData.append('password', loginPassword);

    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'login.php', true);
    xhr.onload = function() {
        if (xhr.status === 200) {
            try {
                let response = JSON.parse(xhr.responseText);
                if (response.status === "success") {
                    alert(`Bem-vindo(a) ao Facilita Emprego, ${response.user.name}!`);
                    document.getElementById("greeting").style.display = "block";
                    window.location.href = "aposlog.html";
                } else {
                    alert("Email ou senha incorretos. Tente novamente.");
                }
            } catch (e) {
                console.error("Erro ao analisar JSON: " + e.message);
                alert("Erro ao processar a resposta do servidor. Tente novamente.");
            }
        } else {
            alert("Erro de login. Tente novamente.");
            console.log(xhr.responseText);
        }
    };
    xhr.onerror = function() {
        alert("Erro de comunicação com o servidor. Tente novamente.");
    };
    xhr.send(formData);
}

// Toggle de visibilidade da senha para cadastro
document.querySelector('.toggle-password').addEventListener('click', function() {
    const passwordInput = document.getElementById('registerPassword');
    const currentType = passwordInput.getAttribute('type');
    const newType = currentType === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', newType);
    this.classList.toggle('bxs-low-vision');
    this.classList.toggle('bxs-show');
});

// Toggle de visibilidade da senha para login
document.querySelector('.toggle-passwordd').addEventListener('click', function() {
    const passwordInput = document.getElementById('loginPassword');
    const currentType = passwordInput.getAttribute('type');
    const newType = currentType === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', newType);
    this.classList.toggle('bxs-low-vision');
    this.classList.toggle('bxs-show');
});
