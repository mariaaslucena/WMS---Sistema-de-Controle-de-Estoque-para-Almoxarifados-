<?php
declare(strict_types=1);
namespace App\Controllers;
use App\Core\Auth;
use App\Core\Controller;
use App\Models\Categoria;
use App\Models\Produto;
final class ProdutoController extends Controller
{
    public function index(): void
    {
        Auth::requireGestor();
        $model = new Produto();
        $editar = null;
        $id = (int) ($_GET['id'] ?? 0);
        if ($id > 0) { $editar = $model->buscarPorId($id); }
        $this->view('produtos/index', ['title' => 'Produtos - WMS', 'produtos' => $model->listar(), 'categorias' => (new Categoria())->listar(), 'editar' => $editar]);
    }
    public function salvar(): void
    {
        Auth::requireGestor();
        $id = (int) ($_POST['id'] ?? 0);
        $nome = trim((string) ($_POST['nome'] ?? ''));
        $descricao = trim((string) ($_POST['descricao'] ?? ''));
        $unidade = strtoupper(trim((string) ($_POST['unidade_medida'] ?? 'UN')));
        $minimo = str_replace(',', '.', trim((string) ($_POST['estoque_minimo'] ?? '0')));
        $categoriaId = (int) ($_POST['categoria_id'] ?? 0);
        if ($nome === '') { $this->flash('erro', 'Informe o nome do produto.'); $this->redirect('/produtos', $id > 0 ? ['id' => $id] : []); }
        if ($unidade === '') { $unidade = 'UN'; }
        if (!is_numeric($minimo) || (float) $minimo < 0) { $this->flash('erro', 'O estoque mínimo deve ser um número igual ou maior que zero.'); $this->redirect('/produtos', $id > 0 ? ['id' => $id] : []); }
        $dados = ['categoria_id' => $categoriaId > 0 ? $categoriaId : null, 'nome' => $nome, 'descricao' => $descricao !== '' ? $descricao : null, 'unidade_medida' => substr($unidade, 0, 30), 'estoque_minimo' => number_format((float) $minimo, 3, '.', '')];
        $model = new Produto();
        if ($id > 0) { $model->atualizar($id, $dados); $this->flash('sucesso', 'Produto atualizado com sucesso.'); }
        else { $model->criar($dados); $this->flash('sucesso', 'Produto cadastrado com sucesso.'); }
        $this->redirect('/produtos');
    }
    public function alterarStatus(): void
    {
        Auth::requireGestor();
        $id = (int) ($_POST['id'] ?? 0);
        $ativo = (int) ($_POST['ativo'] ?? 0) === 1;
        if ($id > 0) { (new Produto())->alterarStatus($id, $ativo); $this->flash('sucesso', $ativo ? 'Produto ativado.' : 'Produto desativado.'); }
        $this->redirect('/produtos');
    }
}
