<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

class ApiTransactionController extends AbstractController
{
    private $entityManager;
    private $transactionRepository;

    public function __construct(EntityManagerInterface $entityManager, TransactionRepository $transactionRepository)
    {
        $this->entityManager = $entityManager;
        $this->transactionRepository = $transactionRepository;
    }

    /**
     * @Route("/", name="api_transactions_index", methods={"GET"})
     * @OA\Get(
     *     path="/api/transactions",
     *     summary="Listar todas las transacciones",
     *     @OA\Parameter(
     *         name="currency",
     *         in="query",
     *         description="Filtrar por moneda",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="month",
     *         in="query",
     *         description="Filtrar por mes",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="year",
     *         in="query",
     *         description="Filtrar por año",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="OK",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Transaction")
     *         )
     *     )
     * )
     */
    public function index(Request $request): Response
    {
        $currency = $request->get('currency');
        $month = $request->get('month') ?: date('m');
        $year = $request->get('year') ?: date('Y');

        $transactions = $this->transactionRepository->findByMonthAndYearAndCurrency($month, $year, $currency);

        return $this->json($transactions);
    }

    /**
     * @Route("/new", name="api_transactions_new", methods={"POST"})
     * @OA\Post(
     *     path="/api/transactions/new",
     *     summary="Crear una nueva transacción",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="type", type="string", enum={"income", "expense"}),
     *             @OA\Property(property="amount", type="string"),
     *             @OA\Property(property="currency", type="string", enum={"CLP", "USD"})
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Transacción creada"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Error de validación"
     *     )
     * )
     */
    public function new(Request $request): Response
    {
        try {
            $data = json_decode($request->getContent(), true);

            // Validar el tipo de transacción
            if (!in_array($data['type'], ['income', 'expense'])) {
                throw new \InvalidArgumentException('El tipo de transacción debe ser "income" o "expense"');
            }

            // Validar la moneda
            if (!in_array($data['currency'], ['CLP', 'USD'])) {
                throw new \InvalidArgumentException('La moneda debe ser "CLP" o "USD"');
            }
            
            $transaction = new Transaction();
            $transaction->setType($data['type']);
            $transaction->setAmount($data['amount']);
            $transaction->setCurrency($data['currency']);
            $transaction->setDate(new \DateTime());

            $this->entityManager->persist($transaction);
            $this->entityManager->flush();

            return $this->json($transaction, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * @Route("/{id}/edit", name="api_transactions_edit", methods={"PUT"})
     * @OA\Put(
     *     path="/api/transactions/{id}/edit",
     *     summary="Editar una transacción",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la transacción a editar",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="type", type="string"),
     *             @OA\Property(property="amount", type="string"),
     *             @OA\Property(property="date", type="string", format="date-time"),
     *             @OA\Property(property="currency", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transacción actualizada"
     *     )
     * )
     */
    public function edit(Request $request, $id): Response
    {
        try {
            $transaction = $this->transactionRepository->find($id);
            if (!$transaction) {
                throw $this->createNotFoundException('No se encontró la transacción con el ID: ' . $id);
            }

            $data = json_decode($request->getContent(), true);

            // Validar el tipo de transacción
            if (!in_array($data['type'], ['income', 'expense'])) {
                throw new \InvalidArgumentException('El tipo de transacción debe ser "income" o "expense"');
            }

            // Validar la moneda
            if (!in_array($data['currency'], ['CLP', 'USD'])) {
                throw new \InvalidArgumentException('La moneda debe ser "CLP" o "USD"');
            }

            $transaction->setType($data['type']);
            $transaction->setAmount($data['amount']);
            $transaction->setCurrency($data['currency']);
            $transaction->setDate(new \DateTime($data['date']));

            $this->entityManager->flush();

            return $this->json($transaction);
        } catch (\InvalidArgumentException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Ha ocurrido un error al editar la transacción'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @Route("/{id}/delete", name="api_transactions_delete", methods={"DELETE"})
     * @OA\Delete(
     *     path="/api/transactions/{id}/delete",
     *     summary="Eliminar una transacción",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la transacción a eliminar",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="Transacción eliminada"
     *     )
     * )
     */
    public function delete($id): Response
    {
        $transaction = $this->transactionRepository->find($id);
        if (!$transaction) {
            throw $this->createNotFoundException('La transacción no existe.');
        }

        $transaction->setDeletedAt(new \DateTime());

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
