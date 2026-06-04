# 🏆 Trabalho para Nota — Cadastro de Alunos e Notas (jQuery)

> **Disciplina:** Interfaces Web II — ETEC 2026/1
> **Entrega:** individual · **Arquivo base:** `trabalho/index.html` (já fornecido)
> **O que você recebe pronto:** o HTML das duas telas + os vetores de dados.
> **O que você faz:** **todo o JavaScript com jQuery**, no bloco `// ✏️ SEU CÓDIGO AQUI`.

---

## 🎯 O que é para construir

Um pequeno sistema de **duas telas** (uma SPA, como nas Aulas 3 e 4 — você troca de tela mostrando/escondendo `<section>`, **sem recarregar a página**):

**Tela 1 — Aluno.** Mostra um aluno por vez, com **nome** e **curso**. Tem os botões:
`⏮ Anterior` · `Próximo ⏭` · `✚ Novo` · `💾 Salvar` · `🗑 Excluir` · `📝 Notas`.

**Tela 2 — Notas.** Mostra as **notas do aluno que estava na Tela 1**, cada uma editável, e **um único botão `💾 Salvar`** (mais um `⬅ Voltar`).

> ⚠️ **Sem servidor nesta tarefa.** Tudo vive em **vetores na memória** (`alunos` e `cursos`). Não usa `$.ajax`/PHP. É normal os dados "voltarem ao original" quando você recarrega a página (F5) — **persistir não é exigido** neste trabalho. O foco é manipular **vetores + DOM + eventos** com jQuery.

---

## 📦 Os dados (já estão no HTML)

```js
var cursos = [ { id:1, nome:'Informática para Internet' }, ... ];   // cursos VÁLIDOS
var alunos = [ { id:1, nome:'Ana Souza', cursoId:1, notas:[ {disciplina,nota}, ... ] }, ... ];
```

Repare na relação: **`aluno.cursoId` aponta para um `id` do vetor `cursos`** (igual chave estrangeira do banco de dados). Para **mostrar** o curso do aluno, você procura o nome no vetor `cursos`. Para **escolher** o curso, o aluno só pode pegar um que exista — por isso o campo curso é um `<select>` montado a partir de `cursos`.

---

## ✅ Requisitos funcionais (checklist de correção)

### Tela 1 — Aluno
- [ ] **R1.** Ao abrir, o `<select>` de curso está **preenchido com os cursos** do vetor `cursos`.
- [ ] **R2.** Ao abrir, aparece o **1º aluno** com nome e curso corretos, e o texto "Registro 1 de N".
- [ ] **R3.** **Anterior / Próximo** navegam pelos alunos **sem estourar** (não passa do primeiro nem do último).
- [ ] **R4.** **Novo** limpa o formulário para cadastrar (sem id).
- [ ] **R5.** **Salvar inteligente:** sem id → **cria** um aluno novo no vetor; com id → **edita** o aluno atual.
- [ ] **R6.** **Curso válido obrigatório:** não dá para salvar sem um curso selecionado (e o curso precisa existir em `cursos`).
- [ ] **R7.** Nome **não pode ser vazio** (borda vermelha + não salva).
- [ ] **R8.** **Excluir** remove o aluno atual do vetor **com `confirm`** e mostra outro (ou esvazia se não houver mais).

### Tela 2 — Notas
- [ ] **R9.** **Notas** abre a Tela 2 mostrando o **nome do aluno** da Tela 1 e **as notas dele** (uma linha por nota).
- [ ] **R10.** Cada nota aparece num **input editável**.
- [ ] **R11.** **Salvar** lê os inputs e grava os valores **de volta no vetor** (`alunos[pos].notas`).
- [ ] **R12.** **Voltar** retorna à Tela 1.

> 💡 Teste rápido do R11: edite uma nota, Salvar, Voltar, entre em Notas de novo → a nota editada **continua** lá (ficou no vetor).

---

## 🧮 Como a nota é distribuída (total 10,0)

| Item | Vale |
|---|---|
| R1 — `<select>` populado a partir de `cursos` | 1,0 |
| R2 — exibir aluno + curso corretos (lookup do nome do curso) | 1,5 |
| R3 — navegação Anterior/Próximo sem estourar | 1,5 |
| R4 + R5 — Novo + Salvar inteligente (cria **e** edita) | 2,0 |
| R6 + R7 — validações (curso válido + nome obrigatório) | 1,0 |
| R8 — Excluir com confirmação | 1,0 |
| R9 + R10 — Tela 2 lista as notas do aluno certo, editáveis | 1,0 |
| R11 + R12 — Salvar notas no vetor + Voltar | 1,0 |
| Organização e clareza do código (nomes, funções, sem repetição) | — |

> 🏅 **Pontos de destaque (bônus, até +0,5):** confirmar antes de descartar alterações; mostrar a **média** das notas na Tela 2; impedir nota fora de 0–10; ordenar alunos por nome.

---

## 💡 Dicas e exemplos (use como ponto de partida, não copie cego)

Estas dicas mostram o **padrão**; você junta as peças e adapta aos nomes do HTML.

### 1) Popular o `<select>` de cursos (R1)
```js
$.each(cursos, function (i, c) {
  $('#curso').append('<option value="' + c.id + '">' + c.nome + '</option>');
});
```

### 2) Mostrar o aluno da posição `pos` (R2)
```js
function mostrar() {
  if (alunos.length === 0) { /* trate o caso "lista vazia" */ return; }
  var a = alunos[pos];
  $('#id').val(a.id);
  $('#nome').val(a.nome).removeClass('erro');
  $('#curso').val(a.cursoId);                 // seleciona a <option> certa pelo id
  $('#pos').text(pos + 1);
  $('#total').text(alunos.length);
  $('#idAtual').text('(id ' + a.id + ')');
}
```
> 🔎 Para **exibir o nome** do curso (se precisar em texto), procure no vetor:
> ```js
> function nomeCurso(cursoId) {
>   var achado = cursos.filter(function (c) { return c.id === cursoId; })[0];
>   return achado ? achado.nome : '—';
> }
> ```

### 3) Anterior / Próximo sem estourar (R3)
```js
$('#anterior').on('click', function () { if (pos > 0) { pos--; mostrar(); } });
$('#proximo').on('click',  function () { if (pos < alunos.length - 1) { pos++; mostrar(); } });
```

### 4) Novo — limpar para cadastrar (R4)
```js
$('#novo').on('click', function () {
  $('#id').val('');                 // sem id => será CRIAÇÃO no Salvar
  $('#nome').val('').removeClass('erro');
  $('#curso').prop('selectedIndex', 0);
  $('#idAtual').text('(novo)');
  $('#nome').focus();
});
```

### 5) Salvar inteligente — cria OU edita (R5, R6, R7)
Mesma lógica da Aula 4: **se tem id, edita; se não tem, cria.**
```js
$('#salvar').on('click', function () {
  var nome  = $('#nome').val().trim();
  var cursoId = parseInt($('#curso').val(), 10);

  // validações
  if (nome === '') { $('#nome').addClass('erro').focus(); return aviso('Nome obrigatório'); }
  if (!cursoId)    { return aviso('Escolha um curso válido'); }   // curso vazio/ inválido

  var id = $('#id').val();
  if (id) {
    // EDITAR o aluno atual
    alunos[pos].nome = nome;
    alunos[pos].cursoId = cursoId;
    aviso('Aluno editado ✅');
  } else {
    // CRIAR aluno novo (com vetor de notas vazio ou padrão)
    alunos.push({ id: proximoId++, nome: nome, cursoId: cursoId, notas: [] });
    pos = alunos.length - 1;        // pula para o recém-criado
    aviso('Aluno cadastrado ✅');
  }
  mostrar();
});
```
> ⚠️ Aluno criado começa **sem notas** (`notas: []`). Pense em como sua Tela 2 mostra "ainda não há notas".

### 6) Excluir com confirmação (R8)
```js
$('#excluir').on('click', function () {
  if (alunos.length === 0) return;
  if (!confirm('Excluir o aluno "' + alunos[pos].nome + '"?')) return;
  alunos.splice(pos, 1);            // remove 1 item na posição pos
  if (pos > 0) pos--;              // recua se apagou o último
  mostrar();
  aviso('Aluno excluído 🗑');
});
```

### 7) Ir para a Tela 2 e listar as notas (R9, R10)
Trocar de tela = esconder uma `<section>` e mostrar a outra.
```js
$('#irNotas').on('click', function () {
  var a = alunos[pos];
  $('#notasAluno').text(a.nome);
  var $corpo = $('#tabelaNotas').empty();
  $.each(a.notas, function (i, n) {
    $corpo.append(
      '<tr><td>' + n.disciplina + '</td>' +
      '<td><input type="number" step="0.1" min="0" max="10" data-i="' + i + '" value="' + n.nota + '"></td></tr>'
    );
  });
  $('#tela-aluno').addClass('hidden');
  $('#tela-notas').removeClass('hidden');
});
```
> 🧠 O `data-i="..."` guarda **qual posição** do vetor `notas` aquele input representa — você usa isso na hora de salvar.

### 8) Salvar as notas de volta no vetor (R11) e Voltar (R12)
```js
$('#salvarNotas').on('click', function () {
  $('#tabelaNotas input').each(function () {
    var i = $(this).data('i');                 // posição no vetor de notas
    alunos[pos].notas[i].nota = parseFloat($(this).val());
  });
  alert('Notas salvas!');                       // (ou use aviso(), se aparecer na Tela 2)
});

$('#voltar').on('click', function () {
  $('#tela-notas').addClass('hidden');
  $('#tela-aluno').removeClass('hidden');
});
```

---

## 🧭 Ordem sugerida de ataque

1. Faça o **select** (dica 1) e o **mostrar()** (dica 2) → tela abre certa. Já vale R1+R2.
2. **Navegação** (dica 3) → R3.
3. **Novo + Salvar** (dicas 4 e 5) → R4..R7 (o item mais pesado da nota).
4. **Excluir** (dica 6) → R8.
5. **Tela 2** (dicas 7 e 8) → R9..R12.
6. Releia o **checklist** e teste cada item antes de entregar.

---

## 📤 Entrega

- Renomeie o arquivo para **`trabalho_SEU-RM.html`** (ex.: `trabalho_12345.html`).
- Confira que abre **sem erro no Console (F12)** e que o checklist passa.
- Envie por **e-mail** com o assunto **`IWII TRABALHO: [SEU NOME] - RM: [SEU RM]`** (anexe o `.html`).
  *Alternativa:* publicar na sua pasta `https://curso.chr.eti.br/SEU_RM/` e mandar a URL.

**Regras:**
- Trabalho **individual**. Pode consultar as Aulas 1–4 e a documentação do jQuery.
- O HTML e os vetores **não devem ser alterados na estrutura** (você pode acrescentar elementos se precisar, mas mantenha os `id` existentes).
- Vale mais um app **com menos recursos, mas funcionando e explicável**, do que muito código quebrado. Capriche no que entregar.

Bom trabalho! 🚀
