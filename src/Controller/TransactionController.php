<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Form\TransactionType;
use App\Repository\TransactionRepository;
use Doctrine\ORM\EntityManagerInterface;
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

    public function index(): Response
    {
        $transactions = $this->transactionRepository->findAll();
        $totalIncome = $this->transactionRepository->calculateTotalByType('income');
        $totalExpense = $this->transactionRepository->calculateTotalByType('expense');
        $totalBalance = $totalIncome - $totalExpense;

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
            $this->entityManager->persist($transaction);
            $this->entityManager->flush();

            return $this->redirectToRoute('transaction_index');
        }

        return $this->render('transaction/new.html.twig', [
            'transaction' => $transaction,
            'form' => $form->createView(),
        ]);
    }
}

