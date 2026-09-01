<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Auth;
use App\Core\Controller;
use App\Models\Categoria;
final class CategoriaController extends Controller
{
    public function index(): void
    {
        Auth::requireGestor();
        $model = new Categoria();
        $editar = null;
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) { $editar = $model->buscarPorId($id); }
        $this->view('categorias/index', ['title' => 'Categorias - WMS', 'categorias' => $model->listar(), 'editar' => $editar]);
    }
    public function salvar(): void
    {
        Auth::requireGestor();
        $id = (int) ($_POST['id'] ?? 0);
        $nome = trim((string) ($_POST['nome'] ?? ''));
        if ($nome === '') { $this->flash('erro', 'Informe o nome da categoria.'); $this->redirect('/categorias', $id > 0 ? ['id' => $id] : []); }
        $model = new Categoria();
        if ($model->nomeEmUso($nome, $id > 0 ? $id : null)) { $this->flash('erro', 'Já existe uma categoria com esse nome.'); $this->redirect('/categorias', $id > 0 ? ['id' => $id] : []); }
        if ($id > 0) { $model->atualizar($id, $nome); $this->flash('sucesso', 'Categoria atualizada com sucesso.'); }
        else { $model->criar($nome); $this->flash('sucesso', 'Categoria cadastrada com sucesso.'); }
        $this->redirect('/categorias');
    }
    public function alterarStatus(): void
    {
        Auth::requireGestor();
        $id = (int) ($_POST['id'] ?? 0);
        $ativo = (int) ($_POST['ativo'] ?? 0) === 1;
        if ($id > 0) { (new Categoria())->alterarStatus($id, $ativo); $this->flash('sucesso', $ativo ? 'Categoria ativada.' : 'Categoria desativada.'); }
        $this->redirect('/categorias');
    }
}
