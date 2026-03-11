<?php
session_start();

/* Inicializa arrays */
if (!isset($_SESSION['nomes'])) {
    $_SESSION['nomes'] = array();
}

if (!isset($_SESSION['notas'])) {
    $_SESSION['notas'] = array();
}

if (!isset($_SESSION['idades'])) {
    $_SESSION['idades'] = array();
}

if (!isset($_SESSION['cursos'])) {
    $_SESSION['cursos'] = array();
}

$mensagem = '';
$tipo_msg = '';
$resultado_busca = null;
$media = null;

/* definir ação */
if (isset($_POST['acao'])) {
    $acao = $_POST['acao'];
} elseif (isset($_GET['acao'])) {
    $acao = $_GET['acao'];
} else {
    $acao = 'menu';
}

/* PROCESSAR AÇÕES */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($acao == 'cadastrar') {

        $nome = isset($_POST['nome']) ? trim($_POST['nome']) : '';
        $idade = isset($_POST['idade']) ? $_POST['idade'] : '';
        $curso = isset($_POST['curso']) ? $_POST['curso'] : '';
        $nota = isset($_POST['nota']) ? $_POST['nota'] : '';
    
        if ($nome == '' || $nota === '' || $idade == '' || $curso == '') {
    
            $mensagem = 'Preencha todos os campos!';
            $tipo_msg = 'erro';
    
        } elseif (!is_numeric($nota) || $nota < 0 || $nota > 10) {
    
            $mensagem = 'A nota deve ser entre 0 e 10!';
            $tipo_msg = 'erro';
    
        } else {
    
            $_SESSION['nomes'][] = $nome;
            $_SESSION['idades'][] = $idade;
            $_SESSION['cursos'][] = $curso;
            $_SESSION['notas'][] = (float)$nota;
    
            $mensagem = 'Aluno cadastrado com sucesso!';
            $tipo_msg = 'sucesso';
    
            $acao = 'menu';  // 👈 ISSO FAZ VOLTAR PARA O MENU
        }
    }

    /* BUSCAR */
    if ($acao == 'buscar_executar') {

        $busca = isset($_POST['busca']) ? trim($_POST['busca']) : '';
    
        $encontrados = array();
    
        if (!empty($_SESSION['nomes'])) {
    
            foreach ($_SESSION['nomes'] as $i => $nome) {
    
                if (stripos($nome, $busca) !== false) {
    
                    $encontrados[] = array(
                        'nome' => $nome,
                        'idade' => $_SESSION['idades'][$i],
                        'curso' => $_SESSION['cursos'][$i],
                        'nota' => $_SESSION['notas'][$i]
                    );
    
                }
    
            }
    
        }
    
        $resultado_busca = $encontrados;
        $acao = 'buscar';
    }

    /* LIMPAR LISTA */
    if ($acao == 'limpar') {

        $_SESSION['nomes'] = array();
        $_SESSION['idades'] = array();
        $_SESSION['cursos'] = array();
        $_SESSION['notas'] = array();
    
        $mensagem = 'Lista limpa!';
        $tipo_msg = 'sucesso';
        $acao = 'menu';
    }
    
}

/* CALCULAR MÉDIA */
if ($acao == 'media') {

    if (count($_SESSION['notas']) > 0) {

        $media = array_sum($_SESSION['notas']) / count($_SESSION['notas']);

    } else {

        $mensagem = 'Nenhum aluno cadastrado!';
        $tipo_msg = 'erro';
        $acao = 'menu';
    }
}

$total_alunos = count($_SESSION['nomes']);


?>



<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Alunos</title>
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --bg: #080c14;
            --surface: #0f1623;
            --border: #1e2d45;
            --gold: #f0c040;
            --gold-dim: #a07820;
            --cyan: #40d0f0;
            --red: #f04060;
            --green: #40e090;
            --text: #c8d8f0;
            --text-dim: #5a7090;
            --mono: 'Space Mono', monospace;
            --sans: 'Syne', sans-serif;
        }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: var(--mono);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background-image:
                radial-gradient(ellipse 80% 50% at 50% -10%, rgba(64,208,240,0.07) 0%, transparent 70%),
                linear-gradient(rgba(240,192,64,0.015) 1px, transparent 1px),
                linear-gradient(90deg, rgba(240,192,64,0.015) 1px, transparent 1px);
            background-size: 100% 100%, 50px 50px, 50px 50px;
        }

        .container {
            width: 100%;
            max-width: 560px;
        }

        /* HEADER */
        .header {
            text-align: center;
            margin-bottom: 32px;
        }
        .header-badge {
            display: inline-block;
            font-family: var(--mono);
            font-size: 10px;
            letter-spacing: 4px;
            color: var(--gold);
            text-transform: uppercase;
            border: 1px solid var(--gold-dim);
            padding: 4px 14px;
            margin-bottom: 16px;
            opacity: 0.8;
        }
        .header h1 {
            font-family: var(--sans);
            font-size: 32px;
            font-weight: 800;
            color: #fff;
            letter-spacing: -1px;
            line-height: 1;
        }
        .header h1 span { color: var(--gold); }
        .header-sub {
            font-size: 11px;
            color: var(--text-dim);
            margin-top: 8px;
            letter-spacing: 2px;
        }
        .stat-pill {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(64,208,240,0.08);
            border: 1px solid rgba(64,208,240,0.2);
            color: var(--cyan);
            font-size: 11px;
            padding: 4px 12px;
            border-radius: 100px;
            margin-top: 14px;
        }
        .stat-pill strong { font-size: 13px; }

        /* CARD */
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 0 60px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.03) inset;
        }

        /* SECTION TITLE */
        .section-title {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 20px 24px 16px;
            border-bottom: 1px solid var(--border);
        }
        .section-title .icon {
            width: 32px; height: 32px;
            border-radius: 8px;
            background: rgba(240,192,64,0.12);
            border: 1px solid rgba(240,192,64,0.25);
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
        }
        .section-title h2 {
            font-family: var(--sans);
            font-size: 16px;
            font-weight: 600;
            color: #fff;
        }
        .section-title .back-btn {
            margin-left: auto;
            background: none;
            border: 1px solid var(--border);
            color: var(--text-dim);
            font-family: var(--mono);
            font-size: 10px;
            padding: 5px 12px;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            letter-spacing: 1px;
            transition: all 0.2s;
        }
        .section-title .back-btn:hover {
            border-color: var(--gold-dim);
            color: var(--gold);
        }

        /* MENU */
        .menu-list { padding: 12px; display: flex; flex-direction: column; gap: 4px; }

        .menu-item {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border-radius: 10px;
            border: 1px solid transparent;
            cursor: pointer;
            text-decoration: none;
            color: var(--text);
            font-family: var(--mono);
            font-size: 13px;
            transition: all 0.18s;
            position: relative;
            overflow: hidden;
            background: none;
            width: 100%;
            text-align: left;
        }
        .menu-item::before {
            content: '';
            position: absolute;
            left: 0; top: 0; bottom: 0;
            width: 3px;
            background: var(--gold);
            border-radius: 0 3px 3px 0;
            transform: scaleY(0);
            transition: transform 0.18s;
        }
        .menu-item:hover {
            background: rgba(240,192,64,0.06);
            border-color: rgba(240,192,64,0.2);
            color: #fff;
        }
        .menu-item:hover::before { transform: scaleY(1); }
        .menu-item:hover .menu-num { color: var(--gold); }

        .menu-item.danger:hover {
            background: rgba(240,64,96,0.07);
            border-color: rgba(240,64,96,0.25);
        }
        .menu-item.danger:hover::before { background: var(--red); }
        .menu-item.danger:hover .menu-num { color: var(--red); }

        .menu-num {
            font-size: 10px;
            color: var(--text-dim);
            width: 18px;
            transition: color 0.18s;
            font-weight: 700;
            letter-spacing: 1px;
        }
        .menu-icon {
            width: 34px; height: 34px;
            border-radius: 8px;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }
        .menu-arrow {
            margin-left: auto;
            color: var(--text-dim);
            font-size: 12px;
            transition: transform 0.18s;
        }
        .menu-item:hover .menu-arrow { transform: translateX(4px); }

        /* FORMS */
        .form-body { padding: 24px; display: flex; flex-direction: column; gap: 16px; }

        .field label {
            display: block;
            font-size: 10px;
            letter-spacing: 2px;
            color: var(--text-dim);
            text-transform: uppercase;
            margin-bottom: 8px;
        }
        .field input {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: 8px;
            padding: 12px 14px;
            color: #fff;
            font-family: var(--mono);
            font-size: 14px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }
        .field input:focus {
            border-color: var(--gold-dim);
            box-shadow: 0 0 0 3px rgba(240,192,64,0.08);
        }
        .field input::placeholder { color: var(--text-dim); }

        .btn {
            padding: 13px 20px;
            border-radius: 8px;
            border: none;
            font-family: var(--mono);
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-primary {
            background: var(--gold);
            color: #0a0a0f;
        }
        .btn-primary:hover {
            background: #ffe080;
            box-shadow: 0 0 20px rgba(240,192,64,0.3);
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: transparent;
            border: 1px solid var(--border);
            color: var(--text-dim);
        }
        .btn-secondary:hover { border-color: var(--text-dim); color: var(--text); }

        /* MENSAGEM */
        .mensagem {
            margin: 0 12px 12px;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .mensagem.sucesso {
            background: rgba(64,224,144,0.1);
            border: 1px solid rgba(64,224,144,0.25);
            color: var(--green);
        }
        .mensagem.erro {
            background: rgba(240,64,96,0.1);
            border: 1px solid rgba(240,64,96,0.25);
            color: var(--red);
        }

        /* LISTA */
        .lista-body { padding: 16px 12px; display: flex; flex-direction: column; gap: 6px; }
        .aluno-row {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 14px;
            border-radius: 8px;
            background: rgba(255,255,255,0.025);
            border: 1px solid var(--border);
        }
        .aluno-idx {
            font-size: 10px;
            color: var(--text-dim);
            width: 20px;
            text-align: right;
        }
        .aluno-nome { flex: 1; font-size: 13px; }
        .aluno-nota {
            font-size: 12px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 100px;
        }
        .nota-alta { background: rgba(64,224,144,0.15); color: var(--green); }
        .nota-media { background: rgba(240,192,64,0.15); color: var(--gold); }
        .nota-baixa { background: rgba(240,64,96,0.15); color: var(--red); }

        .empty-state {
            padding: 40px 24px;
            text-align: center;
            color: var(--text-dim);
            font-size: 12px;
            letter-spacing: 1px;
        }
        .empty-state .emoji { font-size: 32px; display: block; margin-bottom: 12px; }

        /* MÉDIA */
        .media-display {
            padding: 40px 24px;
            text-align: center;
        }
        .media-valor {
            font-family: var(--sans);
            font-size: 72px;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 8px;
        }
        .media-label {
            font-size: 10px;
            letter-spacing: 3px;
            color: var(--text-dim);
            text-transform: uppercase;
        }
        .media-bar-wrap {
            margin: 24px 0 8px;
            background: rgba(255,255,255,0.05);
            border-radius: 100px;
            height: 6px;
            overflow: hidden;
        }
        .media-bar {
            height: 100%;
            border-radius: 100px;
            transition: width 0.6s ease;
        }

        /* BUSCA resultado */
        .busca-result { padding: 12px; display: flex; flex-direction: column; gap: 6px; }
        .nenhum-result {
            padding: 20px 24px;
            text-align: center;
            font-size: 12px;
            color: var(--red);
            letter-spacing: 1px;
        }

        /* SAIR */
        .sair-body {
            padding: 32px 24px;
            text-align: center;
        }
        .sair-body p {
            font-size: 13px;
            color: var(--text-dim);
            margin-bottom: 24px;
            line-height: 1.7;
        }
        .sair-body .big-icon { font-size: 40px; display: block; margin-bottom: 16px; }

        .divider { height: 1px; background: var(--border); margin: 0 12px; }

        /* FOOTER */
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 10px;
            color: var(--text-dim);
            letter-spacing: 2px;
        }

        .aluno-idade{
            font-size:12px;
            color:#5a7090;
            margin-left:10px;
        }

        .aluno-curso{
            font-size:12px;
            background:rgba(64,208,240,0.12);
            border:1px solid rgba(64,208,240,0.25);
            color:#40d0f0;
            padding:3px 10px;
            border-radius:100px;
            margin-left:8px;
        }
        .aluno-idade{
            font-size:13px;
            font-weight:700;
            background:rgba(226, 222, 212, 0.15);
            border:1px solid rgba(224, 209, 168, 0.35);
            color: ##1B5FDB;
            padding:3px 10px;
            border-radius:100px;
        }
    </style>
</head>
<body>

<div class="container">

    <!-- HEADER -->
    <div class="header">
        <div class="header-badge">Sistema Acadêmico</div>
        <h1>Gestão de <span>Alunos</span></h1>
        <div class="header-sub">PAINEL DE CONTROLE</div>
        <div class="stat-pill">
            <strong><?= $total_alunos ?></strong>
            aluno<?= $total_alunos !== 1 ? 's' : '' ?> cadastrado<?= $total_alunos !== 1 ? 's' : '' ?>
        </div>
    </div>

    <!-- CARD PRINCIPAL -->
    <div class="card">

        <?php if ($mensagem): ?>
        <div class="mensagem <?= $tipo_msg ?>">
            <?= $tipo_msg === 'sucesso' ? '✓' : '✕' ?>
            <?= htmlspecialchars($mensagem) ?>
        </div>
        <?php endif; ?>

        <!-- ===================== MENU ===================== -->
        <?php if ($acao == 'menu'): ?>
        <div class="section-title">
            <div class="icon">☰</div>
            <h2>Menu Principal</h2>
        </div>
        <div class="menu-list">
            <form method="POST" style="contents">
                <button type="submit" name="acao" value="cadastrar_form" class="menu-item">
                    <span class="menu-num">01</span>
                    <span class="menu-icon">✦</span>
                    Cadastrar aluno
                    <span class="menu-arrow">→</span>
                </button>
                <button type="submit" name="acao" value="listar" class="menu-item">
                    <span class="menu-num">02</span>
                    <span class="menu-icon">◈</span>
                    Listar alunos
                    <span class="menu-arrow">→</span>
                </button>
                <button type="submit" name="acao" value="buscar" class="menu-item">
                    <span class="menu-num">03</span>
                    <span class="menu-icon">◎</span>
                    Buscar aluno pelo nome
                    <span class="menu-arrow">→</span>
                </button>
                <button type="submit" name="acao" value="media" class="menu-item">
                    <span class="menu-num">04</span>
                    <span class="menu-icon">◇</span>
                    Calcular média da turma
                    <span class="menu-arrow">→</span>
                </button>
                <div class="divider"></div>
                <button type="submit" name="acao" value="sair" class="menu-item danger">
                    <span class="menu-num">05</span>
                    <span class="menu-icon">⊗</span>
                    Sair
                    <span class="menu-arrow">→</span>
                </button>
            </form>
        </div>

        <!-- ===================== CADASTRAR FORM ===================== -->
        <?php elseif ($acao == 'cadastrar_form' || ($acao == 'cadastrar' && $tipo_msg == 'erro')): ?>
        <div class="section-title">
            <div class="icon">✦</div>
            <h2>Cadastrar Aluno</h2>
            <form method="POST"><button type="submit" name="acao" value="menu" class="back-btn">← voltar</button></form>
        </div>
        <form method="POST">
            <div class="form-body">
                <div class="field">
                    <label>Nome do aluno</label>
                    <input type="text" name="nome" placeholder="Ex: Maria Silva" value="<?php echo htmlspecialchars(isset($_POST['nome']) ? $_POST['nome'] : ''); ?>">
                </div>


                
            <div class="field">
                <label>Idade</label>
                <input type="number" name="idade" placeholder="Ex: 20">
            </div>

            <div class="field">
                <label>Curso</label>
                <input type="text" name="curso" placeholder="Ex: Informática">
            </div>

            <div class="field">
                <label>Nota (0 — 10)</label>
                <input type="number" name="nota" placeholder="Ex: 8.5" min="0" max="10" step="0.1" value="<?= htmlspecialchars($_POST['nota'] ?? '') ?>">
            </div>
                <button type="submit" name="acao" value="cadastrar" class="btn btn-primary">Cadastrar aluno</button>
            </div>

        </form>

        <!-- ===================== LISTAR ===================== -->
        <?php elseif ($acao == 'listar'): ?>
            
        <div class="section-title">
            <div class="icon">◈</div>
            <h2>Lista de Alunos</h2>
            <form method="POST"><button type="submit" name="acao" value="menu" class="back-btn">← voltar</button></form>
        </div>
        <?php if (empty($_SESSION['nomes'])): ?>
            <div class="empty-state">
                <span class="emoji">📭</span>
                Nenhum aluno cadastrado ainda.
            </div>
        <?php else: ?>
        <div class="lista-body">
        <?php foreach ($_SESSION['nomes'] as $i => $nome):

            $nota = isset($_SESSION['notas'][$i]) ? $_SESSION['notas'][$i] : 0;
            $idade = isset($_SESSION['idades'][$i]) ? $_SESSION['idades'][$i] : '-';
            $curso = isset($_SESSION['cursos'][$i]) ? $_SESSION['cursos'][$i] : '-';

            $cls = $nota >= 7 ? 'nota-alta' : ($nota >= 5 ? 'nota-media' : 'nota-baixa');
        ?>
            <div class="aluno-row">

            <span class="aluno-idx"><?php echo $i + 1; ?></span>

            <span class="aluno-nome"><?php echo htmlspecialchars($nome); ?></span>

            <span class="aluno-idade"><?php echo $idade; ?> anos</span>

            <span class="aluno-curso"><?php echo htmlspecialchars($curso); ?></span>

            <span class="aluno-nota <?php echo $cls; ?>">
        <?php echo number_format($nota,1); ?>
        </span>

    </div>
            <?php endforeach; ?>
        </div>
        <div style="padding: 12px;">
            <form method="POST">
                <button type="submit" name="acao" value="limpar" class="btn btn-secondary" style="width:100%" onclick="return confirm('Tem certeza que deseja limpar a lista?')">Limpar lista</button>
            </form>
        </div>
        <?php endif; ?>

        <?php elseif ($acao == 'buscar'): ?>

            <div class="section-title">
            <div class="icon">◎</div>
            <h2>Buscar Aluno</h2>

        <form method="POST">
        <button type="submit" name="acao" value="menu" class="back-btn">
            ← Voltar
        </button>
    </form>
</div>

<form method="POST">
    <div class="form-body">

        <div class="field">
            <label>Nome ou parte do nome</label>
            <input type="text" name="busca" placeholder="Ex: Maria">
        </div>

        <button type="submit" name="acao" value="buscar_executar" class="btn btn-primary">
            Buscar
        </button>

    </div>
</form>

<?php if ($resultado_busca !== null): ?>

    <?php if (empty($resultado_busca)): ?>

        <div class="nenhum-result">
            Nenhum aluno encontrado
        </div>

    <?php else: ?>

        <div class="busca-result">

            <?php foreach ($resultado_busca as $r): ?>

                <div class="aluno-row">

                    <span class="aluno-nome">
                        <?php echo htmlspecialchars($r['nome']); ?>
                    </span>

                    <span class="aluno-idade">
                        <?php echo $r['idade']; ?> anos
                    </span>

                    <span class="aluno-curso">
                        <?php echo htmlspecialchars($r['curso']); ?>
                    </span>

                    <span class="aluno-nota">
                        <?php echo number_format($r['nota'], 1); ?>
                    </span>

                </div>

            <?php endforeach; ?>

        </div>

    <?php endif; ?>

<?php endif; ?>

        <!-- ===================== MÉDIA ===================== -->
        <?php elseif ($acao == 'media'): ?>
        <div class="section-title">
            <div class="icon">◇</div>
            <h2>Média da Turma</h2>
            <form method="POST"><button type="submit" name="acao" value="menu" class="back-btn">← voltar</button></form>
        </div>
        <?php
            $cor = $media >= 7 ? '#40e090' : ($media >= 5 ? '#f0c040' : '#f04060');
            $pct = ($media / 10) * 100;
        ?>
        <div class="media-display">
            <div class="media-valor" style="color: <?= $cor ?>"><?= number_format($media, 2) ?></div>
            <div class="media-label">média geral · <?= $total_alunos ?> aluno<?= $total_alunos !== 1 ? 's' : '' ?></div>
            <div class="media-bar-wrap">
                <div class="media-bar" style="width: <?= $pct ?>%; background: <?= $cor ?>;"></div>
            </div>
            <div style="font-size:11px; color: var(--text-dim);">
                <?php if ($media >= 7): ?>✓ Turma aprovada
                <?php elseif ($media >= 5): ?>⚠ Turma em recuperação
                <?php else: ?>✕ Turma reprovada
                <?php endif; ?>
            </div>
        </div>

        <!-- ===================== SAIR ===================== -->
        <?php elseif ($acao == 'sair'): ?>
        <div class="section-title">
            <div class="icon">⊗</div>
            <h2>Encerrar Sessão</h2>
            <form method="POST"><button type="submit" name="acao" value="menu" class="back-btn">← voltar</button></form>
        </div>
        <div class="sair-body">
            <span class="big-icon">👋</span>
            <p>Tem certeza que deseja sair?<br>Os dados serão perdidos ao encerrar a sessão.</p>
            <div style="display:flex; gap:10px; justify-content:center;">
                <form method="POST">
                    <button type="submit" name="acao" value="menu" class="btn btn-secondary">Cancelar</button>
                </form>
                <form method="POST">
                    <button type="submit" name="acao" value="encerrar" class="btn btn-primary" style="background: var(--red);">Confirmar saída</button>
                </form>
            </div>
        </div>

        <!-- ===================== ENCERRAR ===================== -->
        <?php elseif ($acao == 'encerrar'): ?>
        <?php session_destroy(); ?>
        <div class="sair-body" style="padding: 48px 24px;">
            <span class="big-icon">✓</span>
            <p style="color: var(--green); font-size:14px;">Sessão encerrada com sucesso!</p>
            <br>
            <form method="POST">
                <button type="submit" name="acao" value="menu" class="btn btn-primary">Novo acesso</button>
            </form>
        </div>

        <?php endif; ?>

    </div><!-- /card -->

    <div class="footer">PHP · SISTEMA DE GESTÃO ACADÊMICA</div>

</div><!-- /container -->

</body>
</html>