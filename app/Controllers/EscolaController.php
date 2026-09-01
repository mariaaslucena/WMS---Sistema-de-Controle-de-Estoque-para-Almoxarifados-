<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Auth;
use App\Core\Controller;
use App\Models\Escola;
final class EscolaController extends Controller
{
    public function index(): void
    {
        Auth::requireGestor();
        $model = new Escola();
        $editar = null;
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) { $editar = $model->buscarPorId($id); }
        $this->view('escolas/index', ['title' => 'Escolas - WMS', 'escolas' => $model->listar(), 'editar' => $editar]);
    }
    public function salvar(): void
    {
        Auth::requireGestor();
        $id = (int) ($_POST['id'] ?? 0);
        $nome = trim((string) ($_POST['nome'] ?? ''));
        $codigo = trim((string) ($_POST['codigo'] ?? ''));
        if ($nome === '') { $this->flash('erro', 'Informe o nome da escola.'); $this->redirect('/escolas', $id > 0 ? ['id' => $id] : []); }
        $model = new Escola();
        if ($model->codigoEmUso($codigo, $id > 0 ? $id : null)) { $this->flash('erro', 'Esse código de escola já está em uso.'); $this->redirect('/escolas', $id > 0 ? ['id' => $id] : []); }
        $codigoDb = $codigo !== '' ? $codigo : null;
        if ($id > 0) { $model->atualizar($id, $nome, $codigoDb); $this->flash('sucesso', 'Escola atualizada com sucesso.'); }
        else { $model->criar($nome, $codigoDb); $this->flash('sucesso', 'Escola cadastrada com sucesso.'); }
        $this->redirect('/escolas');
    }
    public function alterarStatus(): void
    {
        Auth::requireGestor();
        $id = (int) ($_POST['id'] ?? 0);
        $ativo = (int) ($_POST['ativo'] ?? 0) === 1;
        if ($id > 0) { (new Escola())->alterarStatus($id, $ativo); $this->flash('sucesso', $ativo ? 'Escola ativada.' : 'Escola desativada.'); }
        $this->redirect('/escolas');
    }
}
