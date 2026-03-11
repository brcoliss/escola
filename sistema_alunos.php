<?php
session_start();

if (!isset($_SESSION['nomes']))  $_SESSION['nomes']  = array();
if (!isset($_SESSION['notas']))  $_SESSION['notas']  = array();
if (!isset($_SESSION['idades'])) $_SESSION['idades'] = array();
if (!isset($_SESSION['cursos'])) $_SESSION['cursos'] = array();

$msg  = '';
$tmsg = '';
$busca_result = null;

if (isset($_POST['acao']))     $acao = $_POST['acao'];
elseif (isset($_GET['acao']))  $acao = $_GET['acao'];
else                           $acao = 'menu';

if ($acao == 'encerrar') session_destroy();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if ($acao == 'cadastrar') {
        $nome  = isset($_POST['nome'])  ? trim($_POST['nome'])  : '';
        $idade = isset($_POST['idade']) ? trim($_POST['idade']) : '';
        $curso = isset($_POST['curso']) ? trim($_POST['curso']) : '';
        $nota  = isset($_POST['nota'])  ? trim($_POST['nota'])  : '';

        if (count($_SESSION['nomes']) >= 10)                          { $msg = 'Limite de 10 alunos atingido.';       $tmsg = 'erro'; }
        elseif ($nome=='' || $nota==='' || $idade=='' || $curso=='')  { $msg = 'Preencha todos os campos.';           $tmsg = 'erro'; }
        elseif (!is_numeric($nota) || $nota < 0 || $nota > 10)       { $msg = 'Nota deve ser entre 0 e 10.';         $tmsg = 'erro'; }
        else {
            $_SESSION['nomes'][]  = $nome;
            $_SESSION['idades'][] = $idade;
            $_SESSION['cursos'][] = $curso;
            $_SESSION['notas'][]  = (float)$nota;
            $msg = 'Aluno cadastrado!'; $tmsg = 'sucesso'; $acao = 'menu';
        }
    }

    if ($acao == 'buscar_executar') {
        $q = isset($_POST['busca']) ? trim($_POST['busca']) : '';
        $busca_result = array();
        foreach ($_SESSION['nomes'] as $i => $n) {
            if (stripos($n, $q) !== false)
                $busca_result[] = array('nome'=>$n,'idade'=>$_SESSION['idades'][$i],'curso'=>$_SESSION['cursos'][$i],'nota'=>$_SESSION['notas'][$i]);
        }
        $acao = 'buscar';
    }

    if ($acao == 'limpar') {
        $_SESSION['nomes'] = $_SESSION['idades'] = $_SESSION['cursos'] = $_SESSION['notas'] = array();
        $msg = 'Lista limpa.'; $tmsg = 'sucesso'; $acao = 'menu';
    }
}

$total = count($_SESSION['notas']);
$media = $total > 0 ? array_sum($_SESSION['notas']) / $total : 0;
$nmax  = $total > 0 ? max($_SESSION['notas']) : 0;
$nmin  = $total > 0 ? min($_SESSION['notas']) : 0;

function cls_nota($n) {
    if ($n >= 7) return 'badge-green';
    if ($n >= 5) return 'badge-yellow';
    return 'badge-red';
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sistema de Alunos</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
    --bg:#f0f2ff;--white:#fff;
    --blue:#4f6ef7;--blue-l:#eef0ff;--blue-m:#c7cffe;
    --purple:#7c3aed;--purple-l:#f3eeff;--purple-m:#d8b4fe;
    --green:#059669;--green-l:#d1fae5;
    --red:#dc2626;--red-l:#fee2e2;
    --yellow:#d97706;--yellow-l:#fef3c7;
    --txt:#1e1b4b;--txt-m:#6b7280;--txt-l:#9ca3af;
    --border:#e5e7f0;
    --sh:0 8px 32px rgba(79,110,247,.12);
    --r:14px;--f:'Inter',sans-serif
}
body{background:var(--bg);color:var(--txt);font-family:var(--f);min-height:100vh;display:flex;align-items:flex-start;justify-content:center;padding:40px 16px}
.wrap{width:100%;max-width:520px}

.header{text-align:center;margin-bottom:28px}
.logo{display:inline-flex;align-items:center;justify-content:center;width:52px;height:52px;background:linear-gradient(135deg,var(--blue),var(--purple));border-radius:16px;font-size:22px;margin-bottom:14px;box-shadow:0 4px 14px rgba(79,110,247,.35)}
.header h1{font-size:22px;font-weight:700;letter-spacing:-.5px}
.header p{font-size:13px;color:var(--txt-m);margin-top:4px}
.chip{display:inline-flex;align-items:center;gap:5px;background:var(--white);border:1px solid var(--blue-m);color:var(--blue);font-size:12px;font-weight:600;padding:4px 12px;border-radius:100px;margin-top:10px;box-shadow:0 1px 4px rgba(79,110,247,.1)}

.card{background:var(--white);border:1px solid var(--border);border-radius:var(--r);box-shadow:var(--sh);overflow:hidden}

.ch{display:flex;align-items:center;gap:10px;padding:18px 20px;border-bottom:1px solid var(--border)}
.ch-icon{width:34px;height:34px;border-radius:10px;background:var(--blue-l);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.ch h2{font-size:15px;font-weight:600}
.back{margin-left:auto;background:none;border:1px solid var(--border);color:var(--txt-m);font-family:var(--f);font-size:12px;font-weight:500;padding:5px 12px;border-radius:8px;cursor:pointer;transition:.18s}
.back:hover{background:var(--blue-l);border-color:var(--blue-m);color:var(--blue)}

.menu-body{padding:10px}
.mi{display:flex;align-items:center;gap:12px;width:100%;padding:13px 14px;background:none;border:1px solid transparent;border-radius:10px;color:var(--txt);font-family:var(--f);font-size:14px;font-weight:500;cursor:pointer;transition:.16s;text-align:left}
.mi:hover{background:var(--blue-l);border-color:var(--blue-m);color:var(--blue)}
.mi:hover .mn,.mi:hover .ma{color:var(--blue)}
.mi:hover .ma{transform:translateX(3px)}
.mi.danger:hover{background:var(--red-l);border-color:#fca5a5;color:var(--red)}
.mi.danger:hover .mn,.mi.danger:hover .ma{color:var(--red)}
.mn{font-size:11px;font-weight:600;color:var(--txt-l);width:22px;transition:.16s}
.mi-icon{width:36px;height:36px;border-radius:10px;background:var(--bg);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.ml{flex:1}
.ma{color:var(--txt-l);font-size:13px;transition:.16s}
.mdiv{height:1px;background:var(--border);margin:6px 0}

.fb{padding:20px;display:flex;flex-direction:column;gap:14px}
.field label{display:block;font-size:12px;font-weight:600;color:var(--txt-m);margin-bottom:6px;text-transform:uppercase;letter-spacing:.5px}
.field input{width:100%;background:var(--bg);border:1.5px solid var(--border);border-radius:10px;padding:11px 14px;color:var(--txt);font-family:var(--f);font-size:14px;font-weight:500;outline:none;transition:.18s}
.field input:focus{border-color:var(--blue);background:var(--white);box-shadow:0 0 0 3px rgba(79,110,247,.1)}
.field input::placeholder{color:var(--txt-l);font-weight:400}

.btn{padding:11px 18px;border-radius:10px;border:none;font-family:var(--f);font-size:14px;font-weight:600;cursor:pointer;transition:.18s}
.btn-p{background:linear-gradient(135deg,var(--blue),var(--purple));color:#fff;box-shadow:0 2px 8px rgba(79,110,247,.3)}
.btn-p:hover{box-shadow:0 4px 16px rgba(79,110,247,.45);transform:translateY(-1px)}
.btn-s{background:var(--bg);border:1.5px solid var(--border);color:var(--txt-m)}
.btn-s:hover{background:var(--white);border-color:var(--blue-m);color:var(--blue)}
.btn-d{background:var(--red);color:#fff}
.btn-d:hover{background:#b91c1c;transform:translateY(-1px)}

.alert{display:flex;align-items:center;gap:10px;margin:14px 14px 0;padding:11px 14px;border-radius:10px;font-size:13px;font-weight:500}
.adot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
.sucesso{background:var(--green-l);color:var(--green)}
.sucesso .adot{background:var(--green)}
.erro{background:var(--red-l);color:var(--red)}
.erro .adot{background:var(--red)}

.lb{padding:12px;display:flex;flex-direction:column;gap:6px}
.ac{display:flex;align-items:center;gap:10px;padding:12px 14px;border-radius:10px;border:1px solid var(--border);background:var(--white);transition:.16s}
.ac:hover{box-shadow:0 1px 8px rgba(79,110,247,.1)}
.anum{font-size:11px;font-weight:700;color:var(--txt-l);min-width:20px;flex-shrink:0}
.av{width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--blue-l),var(--purple-l));border:1.5px solid var(--blue-m);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:700;color:var(--blue);flex-shrink:0}
.ai{flex:1;min-width:80px}
.aname{font-size:13px;font-weight:600}
.asub{font-size:11px;color:var(--txt-m);margin-top:1px}
.badge{font-size:11px;font-weight:600;padding:3px 9px;border-radius:100px;flex-shrink:0}
.badge-green {background:var(--green-l);color:var(--green);border:1px solid #6ee7b7}
.badge-yellow{background:var(--yellow-l);color:var(--yellow);border:1px solid #fcd34d}
.badge-red   {background:var(--red-l);color:var(--red);border:1px solid #fca5a5}

.empty{padding:40px 20px;text-align:center}
.empty-icon{font-size:36px;margin-bottom:10px}
.empty p{font-size:13px;color:var(--txt-m)}

.mt{padding:32px 24px 20px;text-align:center}
.ring-wrap{position:relative;display:inline-flex;align-items:center;justify-content:center;width:180px;height:180px;margin-bottom:18px}
.ring-wrap svg{position:absolute;top:0;left:0;transform:rotate(-90deg)}
.ring-bg  {fill:none;stroke:var(--border);stroke-width:10}
.ring-fill{fill:none;stroke-width:10;stroke-linecap:round}
.ri{position:relative;z-index:1;text-align:center}
.rn{font-size:48px;font-weight:700;line-height:1;letter-spacing:-2px}
.rs{font-size:11px;color:var(--txt-l);margin-top:3px;font-weight:500;letter-spacing:.5px}
.sbadge{display:inline-flex;align-items:center;gap:6px;padding:6px 16px;border-radius:100px;font-size:12px;font-weight:700;letter-spacing:.5px;margin-bottom:20px}
.sdot{width:6px;height:6px;border-radius:50%}

.mbar{padding:0 24px 16px}
.mbar-top{display:flex;justify-content:space-between;font-size:11px;font-weight:600;color:var(--txt-m);margin-bottom:8px}
.btrack{height:6px;background:var(--bg);border-radius:100px;overflow:hidden;border:1px solid var(--border)}
.bfill{height:100%;border-radius:100px}

.mgrid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;padding:0 12px 20px}
.mbox{background:var(--bg);border:1px solid var(--border);border-radius:12px;padding:14px 10px;text-align:center}
.mval{font-size:22px;font-weight:700;letter-spacing:-.5px;line-height:1}
.mlbl{font-size:10px;font-weight:600;color:var(--txt-l);text-transform:uppercase;letter-spacing:.5px;margin-top:5px}

.sair{padding:36px 24px;text-align:center}
.sair p{font-size:14px;color:var(--txt-m);margin-bottom:24px;line-height:1.6}
.sicon{font-size:42px;margin-bottom:14px;display:block}
.nores{padding:18px 20px;text-align:center;font-size:13px;color:var(--red);font-weight:500}
.p12{padding:12px}
.footer{text-align:center;margin-top:18px;font-size:11px;color:var(--txt-l)}
</style>
</head>
<body>
<div class="wrap">

<div class="header">
    <div class="logo">&#127979;</div>
    <h1>Gestao de Alunos</h1>
    <p>Sistema Academico</p>
    <div class="chip">&#9679; <strong><?php echo $total; ?></strong> aluno<?php echo $total!=1?'s':''; ?> cadastrado<?php echo $total!=1?'s':''; ?></div>
</div>

<div class="card">

<?php if ($msg != ''): ?>
<div class="alert <?php echo $tmsg; ?>"><span class="adot"></span><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<?php if ($acao == 'menu'): ?>
    <div class="ch"><div class="ch-icon">&#9776;</div><h2>Menu Principal</h2></div>
    <div class="menu-body">
    <form method="POST">
        <button type="submit" name="acao" value="cadastrar_form" class="mi"><span class="mn">01</span><span class="mi-icon">&#43;</span><span class="ml">Cadastrar aluno</span><span class="ma">&#8594;</span></button>
        <button type="submit" name="acao" value="listar"         class="mi"><span class="mn">02</span><span class="mi-icon">&#9776;</span><span class="ml">Listar alunos</span><span class="ma">&#8594;</span></button>
        <button type="submit" name="acao" value="buscar"         class="mi"><span class="mn">03</span><span class="mi-icon">&#128269;</span><span class="ml">Buscar aluno pelo nome</span><span class="ma">&#8594;</span></button>
        <button type="submit" name="acao" value="media"          class="mi"><span class="mn">04</span><span class="mi-icon">&#128202;</span><span class="ml">Calcular media da turma</span><span class="ma">&#8594;</span></button>
        <div class="mdiv"></div>
        <button type="submit" name="acao" value="sair" class="mi danger"><span class="mn">05</span><span class="mi-icon">&#128682;</span><span class="ml">Sair</span><span class="ma">&#8594;</span></button>
    </form>
    </div>

<?php elseif ($acao == 'cadastrar_form' || ($acao == 'cadastrar' && $tmsg == 'erro')): ?>
    <div class="ch">
        <div class="ch-icon">&#43;</div><h2>Cadastrar Aluno</h2>
        <form method="POST" style="margin-left:auto"><button name="acao" value="menu" class="back">&#8592; Voltar</button></form>
    </div>
    <form method="POST">
    <div class="fb">
        <div class="field"><label>Nome</label><input type="text" name="nome" placeholder="Ex: Maria Silva" value="<?php echo htmlspecialchars(isset($_POST['nome'])?$_POST['nome']:''); ?>"></div>
        <div class="field"><label>Idade</label><input type="number" name="idade" placeholder="Ex: 20" min="1" max="99" value="<?php echo htmlspecialchars(isset($_POST['idade'])?$_POST['idade']:''); ?>"></div>
        <div class="field"><label>Curso</label><input type="text" name="curso" placeholder="Ex: Informatica" value="<?php echo htmlspecialchars(isset($_POST['curso'])?$_POST['curso']:''); ?>"></div>
        <div class="field"><label>Nota (0 a 10)</label><input type="number" name="nota" placeholder="Ex: 8.5" min="0" max="10" step="0.1" value="<?php echo htmlspecialchars(isset($_POST['nota'])?$_POST['nota']:''); ?>"></div>
        <button type="submit" name="acao" value="cadastrar" class="btn btn-p">Cadastrar aluno</button>
    </div>
    </form>

<?php elseif ($acao == 'listar'): ?>
    <div class="ch">
        <div class="ch-icon">&#9776;</div><h2>Lista de Alunos</h2>
        <form method="POST" style="margin-left:auto"><button name="acao" value="menu" class="back">&#8592; Voltar</button></form>
    </div>
    <?php if (empty($_SESSION['nomes'])): ?>
        <div class="empty"><div class="empty-icon">&#128237;</div><p>Nenhum aluno cadastrado ainda.</p></div>
    <?php else: ?>
        <div class="lb">
        <?php for ($i=0; $i<count($_SESSION['nomes']); $i++):
            $nn = $_SESSION['nomes'][$i]; $nt = (float)$_SESSION['notas'][$i];
            $ni = $_SESSION['idades'][$i]; $nc = $_SESSION['cursos'][$i];
        ?>
            <div class="ac">
                <span class="anum"><?php echo $i+1; ?></span>
                <div class="av"><?php echo strtoupper(substr($nn,0,1)); ?></div>
                <div class="ai">
                    <div class="aname"><?php echo htmlspecialchars($nn); ?></div>
                    <div class="asub"><?php echo htmlspecialchars($nc); ?> &middot; <?php echo $ni; ?> anos</div>
                </div>
                <span class="badge <?php echo cls_nota($nt); ?>"><?php echo number_format($nt,1); ?></span>
            </div>
        <?php endfor; ?>
        </div>
        <div class="p12">
            <form method="POST"><button name="acao" value="limpar" class="btn btn-s" style="width:100%" onclick="return confirm('Limpar lista?')">Limpar lista</button></form>
        </div>
    <?php endif; ?>

<?php elseif ($acao == 'buscar'): ?>
    <div class="ch">
        <div class="ch-icon">&#128269;</div><h2>Buscar Aluno</h2>
        <form method="POST" style="margin-left:auto"><button name="acao" value="menu" class="back">&#8592; Voltar</button></form>
    </div>
    <form method="POST">
    <div class="fb">
        <div class="field"><label>Nome ou parte do nome</label><input type="text" name="busca" placeholder="Ex: Maria" value="<?php echo htmlspecialchars(isset($_POST['busca'])?$_POST['busca']:''); ?>"></div>
        <button type="submit" name="acao" value="buscar_executar" class="btn btn-p">Buscar</button>
    </div>
    </form>
    <?php if ($busca_result !== null): ?>
        <?php if (empty($busca_result)): ?>
            <div class="nores">Nenhum aluno encontrado.</div>
        <?php else: ?>
            <div class="lb">
            <?php foreach ($busca_result as $r):
                $rn = (float)$r['nota'];
            ?>
                <div class="ac">
                    <div class="av"><?php echo strtoupper(substr($r['nome'],0,1)); ?></div>
                    <div class="ai">
                        <div class="aname"><?php echo htmlspecialchars($r['nome']); ?></div>
                        <div class="asub"><?php echo htmlspecialchars($r['curso']); ?> &middot; <?php echo $r['idade']; ?> anos</div>
                    </div>
                    <span class="badge <?php echo cls_nota($rn); ?>"><?php echo number_format($rn,1); ?></span>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

<?php elseif ($acao == 'media'): ?>
    <div class="ch">
        <div class="ch-icon">&#128202;</div><h2>Media da Turma</h2>
        <form method="POST" style="margin-left:auto"><button name="acao" value="menu" class="back">&#8592; Voltar</button></form>
    </div>
    <?php
        if ($total==0)       { $cor='#9ca3af'; $stroke='#9ca3af'; $st='SEM DADOS';       $sbg='#f3f4f6'; $sbd='#e5e7eb'; $sdot='#9ca3af'; }
        elseif ($media>=7)   { $cor='#059669'; $stroke='url(#ga)'; $st='TURMA APROVADA'; $sbg='#d1fae5'; $sbd='#6ee7b7'; $sdot='#059669'; }
        elseif ($media>=5)   { $cor='#d97706'; $stroke='url(#gb)'; $st='EM RECUPERACAO'; $sbg='#fef3c7'; $sbd='#fcd34d'; $sdot='#d97706'; }
        else                 { $cor='#dc2626'; $stroke='url(#gc)'; $st='TURMA REPROVADA'; $sbg='#fee2e2'; $sbd='#fca5a5'; $sdot='#dc2626'; }
        $c   = 2*3.14159265*76;
        $pct = $total>0 ? $media/10 : 0;
        $don = round($c*$pct,2); $doff = round($c-$don,2);
    ?>
    <div class="mt">
        <div class="ring-wrap">
            <svg width="180" height="180" viewBox="0 0 180 180">
                <defs>
                    <linearGradient id="ga" x1="0%" x2="100%"><stop offset="0%" stop-color="#34d399"/><stop offset="100%" stop-color="#059669"/></linearGradient>
                    <linearGradient id="gb" x1="0%" x2="100%"><stop offset="0%" stop-color="#fbbf24"/><stop offset="100%" stop-color="#d97706"/></linearGradient>
                    <linearGradient id="gc" x1="0%" x2="100%"><stop offset="0%" stop-color="#f87171"/><stop offset="100%" stop-color="#dc2626"/></linearGradient>
                </defs>
                <circle class="ring-bg"   cx="90" cy="90" r="76"/>
                <circle class="ring-fill" cx="90" cy="90" r="76" stroke="<?php echo $stroke; ?>" stroke-dasharray="<?php echo $don.' '.$doff; ?>"/>
            </svg>
            <div class="ri">
                <div class="rn" style="color:<?php echo $cor; ?>"><?php echo number_format($media,1); ?></div>
                <div class="rs">DE 10</div>
            </div>
        </div>
        <div>
            <span class="sbadge" style="background:<?php echo $sbg; ?>;border:1px solid <?php echo $sbd; ?>;color:<?php echo $cor; ?>">
                <span class="sdot" style="background:<?php echo $sdot; ?>"></span>
                <?php echo $st; ?>
            </span>
        </div>
    </div>
    <?php if ($total > 0): ?>
    <div class="mbar">
        <div class="mbar-top"><span>Progresso</span><span><?php echo round($pct*100); ?>%</span></div>
        <div class="btrack"><div class="bfill" style="width:<?php echo round($pct*100,1); ?>%;background:<?php echo $cor; ?>"></div></div>
    </div>
    <div class="mgrid">
        <div class="mbox"><div class="mval" style="color:var(--blue)"><?php echo $total; ?></div><div class="mlbl">Alunos</div></div>
        <div class="mbox"><div class="mval" style="color:var(--green)"><?php echo number_format($nmax,1); ?></div><div class="mlbl">Maior nota</div></div>
        <div class="mbox"><div class="mval" style="color:var(--red)"><?php echo number_format($nmin,1); ?></div><div class="mlbl">Menor nota</div></div>
    </div>
    <?php else: ?>
    <div class="empty" style="padding:16px 20px 28px"><div class="empty-icon">&#128202;</div><p>Cadastre alunos para ver as estatisticas.</p></div>
    <?php endif; ?>

<?php elseif ($acao == 'sair'): ?>
    <div class="ch">
        <div class="ch-icon">&#128682;</div><h2>Encerrar Sessao</h2>
        <form method="POST" style="margin-left:auto"><button name="acao" value="menu" class="back">&#8592; Voltar</button></form>
    </div>
    <div class="sair">
        <span class="sicon">&#128075;</span>
        <p>Tem certeza que deseja sair?<br>Os dados serao perdidos.</p>
        <div style="display:flex;gap:10px;justify-content:center">
            <form method="POST"><button name="acao" value="menu" class="btn btn-s">Cancelar</button></form>
            <form method="POST"><button name="acao" value="encerrar" class="btn btn-d">Confirmar</button></form>
        </div>
    </div>

<?php elseif ($acao == 'encerrar'): ?>
    <div class="sair" style="padding:48px 24px">
        <span class="sicon">&#9989;</span>
        <p style="color:var(--green);font-weight:600">Sessao encerrada com sucesso!</p><br>
        <form method="POST"><button name="acao" value="menu" class="btn btn-p">Novo acesso</button></form>
    </div>

<?php endif; ?>

</div>
<div class="footer">Sistema de Gestao Academica &mdash; PHP</div>
</div>
</body>
</html>
