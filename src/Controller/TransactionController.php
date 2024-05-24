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
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

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

        $totalBalance = $this->transactionRepository->calculateTotalBalance($currency);
        
        return $this->render('transaction/index.html.twig', [
            'transactions' => $transactions,
            'total_balance' => $totalBalance
        ]);
    }

    public function new(Request $request, SessionInterface $session): Response
    {
        $transaction = new Transaction();
        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->transactionRepository->add($transaction, true);

                return $this->redirectToRoute('transaction_index');
            } catch (\Doctrine\DBAL\Exception\DriverException $e) {
                $session->getFlashBag()->add('error', 'El monto ingresado supera el valor máximo permitido. Por favor, ingrese un monto válido.');
            }
        }

        return $this->render('transaction/new.html.twig', [
            'transaction' => $transaction,
            'form' => $form->createView(),
        ]);
    }
    
    public function edit(Request $request, $id): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $transaction = $entityManager->getRepository(Transaction::class)->find($id);

        if (!$transaction) {
            throw $this->createNotFoundException('No se encontró la transacción con el ID: '.$id);
        }

        $form = $this->createForm(TransactionType::class, $transaction);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('transaction_index');
        }

        return $this->render('transaction/edit.html.twig', [
            'transaction' => $transaction,
            'form' => $form->createView(),
        ]);
    }
    
    public function delete(Request $request, $id, EntityManagerInterface $entityManager): Response
    {
        $transaction = $entityManager->getRepository(Transaction::class)->find($id);

        if (!$transaction) {
            throw $this->createNotFoundException('La transacción no existe.');
        }

        $transaction->setDeletedAt(new \DateTime());

        $entityManager->persist($transaction);
        $entityManager->flush();

        return $this->redirectToRoute('transaction_index');
    }
}