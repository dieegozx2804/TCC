document.getElementById("recuperarSenhaForm").addEventListener("submit", function(event) {
    event.preventDefault();

    // Recuperar o valor do email
    var email = document.getElementById("email").value;

    // Criar a requisição AJAX
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "recuperar-senha.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    // Enviar o email via POST
    xhr.send("email=" + encodeURIComponent(email));

    // Ao carregar a resposta
    xhr.onload = function() {
        console.log(xhr.responseText);  // Verifique a resposta recebida
        if (xhr.status === 200) {
            try {
                let response = JSON.parse(xhr.responseText);
                console.log(response);  // Verifique se a resposta é um JSON válido

                if (response.status === 'success') {
                    alert("Se o email existir, você receberá um link para redefinir sua senha.");
                } else {
                    alert(response.message); // Mostra a mensagem de erro do PHP
                }
            } catch (e) {
                console.error("Erro ao fazer o parsing do JSON:", e);
                alert("Ocorreu um erro ao processar sua solicitação.");
            }
        } else {
            console.error("Erro na requisição:", xhr.status);
            alert("Erro ao tentar enviar a recuperação de senha. Tente novamente.");
        }
    };
});