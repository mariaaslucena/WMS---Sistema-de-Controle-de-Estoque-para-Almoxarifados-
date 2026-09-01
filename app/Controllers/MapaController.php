<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Models\Escola;
use App\Models\Posicao;
use App\Models\Produto;

final class MapaController extends Controller
{
    private const RUAS = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];

    public function index(): void
    {
        Auth::requireLogin();

        $rua = strtoupper(trim((string) ($_GET['rua'] ?? 'A')));
        if (!in_array($rua, self::RUAS, true)) { $rua = 'A'; }

        $model = new Posicao();
        $posicoes = $model->mapaPorRua($rua);
        $itens = $model->itensPorRua($rua);

        $itensPorPosicao = [];
        foreach ($itens as $item) {
            $posicaoId = (int) $item['posicao_id'];
            $itensPorPosicao[$posicaoId][] = [
                'produto_id' => (int) $item['produto_id'],
                'produto' => (string) $item['produto_nome'],
                'unidade' => (string) $item['unidade_medida'],
                'quantidade' => (float) $item['quantidade'],
            ];
        }

        foreach ($posicoes as &$posicao) {
            $id = (int) $posicao['id'];
            $posicao['itens'] = $itensPorPosicao[$id] ?? [];
            $posicao['ocupada'] = (float) $posicao['quantidade_total'] > 0;
        }
        unset($posicao);

        $this->view('mapa/index', [
            'title' => 'Mapa de Estoque - WMS',
            'ruas' => self::RUAS,
            'ruaAtual' => $rua,
            'posicoes' => $posicoes,
            'resumo' => $model->resumoGeral(),
            'produtos' => (new Produto())->listarAtivos(),
            'escolas' => (new Escola())->listarAtivas(),
        ]);
    }
}
