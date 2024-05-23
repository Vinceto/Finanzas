<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionType;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends AbstractController
{
    private $entityManager;
    private $transactionRepository;

    public function __construct(EntityManagerInterface $entityManager, TransactionRepository $transactionRepository)
    {
        $this->entityManager = $entityManager;
        $this->transactionRepository = $transactionRepository;
    }
    
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $currency = $request->query->get('currency');
        $transactions = $this->transactionRepository->findByCurrency($currency);
        
        // Paginación
        $transactions = $paginator->paginate(
            $transactions, // Query sin ejecutar
            $request->query->getInt('page', 1), // Número de página
            10 // Cantidad de elementos por página
        );
        // dump($transactions);
        // die;
        $totalBalance = $this->transactionRepository->calculateTotalBalance($currency);

        return $this->render('transaction/index.html.twig', [
            'transactions' => $transactions,
            'total_balance' => $totalBalance,
        ]);
    }

    public function new(Request $request): Response
    {
        $transaction = new Transaction();
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->transactionRepository->add($transaction, true);

            return $this->redirectToRoute('transaction_index');
        }

        return $this->render('transaction/new.html.twig', [
            'transaction' => $transaction,
            'form' => $form->createView(),
        ]);
    }
}