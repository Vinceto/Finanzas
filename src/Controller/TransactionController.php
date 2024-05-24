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
        $currency = $request->get('currency') ?: null;
        $month = $request->get('month') ?: date('m');
        $year = $request->get('year') ?: date('Y');

        // Obtener todos los años disponibles para el filtro de año
        $allYears = $this->transactionRepository->findUniqueYears();

        // Definir los nombres de los meses en español
        $spanishMonths = [
            0 => 'Seleccione un Mes',
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        // Crear el arreglo de meses solo si el año es igual al año actual, de lo contrario, mostrar solo diciembre
        $currentYear = date('Y');
        $currentMonth = date('m');
        $allMonths = [];
        $hasta = ($year == $currentYear) ? $currentMonth : 12;
        for ($i = 0; $i <= $hasta; $i++) {
            $monthName = $spanishMonths[$i];
            $allMonths[] = [
                'month' => $i,
                'monthName' => $monthName
            ];
        }

        // Obtener las transacciones filtradas
        $transactions = $this->transactionRepository->findByMonthAndYearAndCurrency($month, $year, $currency);
        
        // Paginar las transacciones
        $transactions = $paginator->paginate(
            $transactions, // Query sin ejecutar
            $request->query->getInt('page', 1), // Número de página
            10 // Cantidad de elementos por página
        );

        // Calcular el balance total
        $totalBalance = $this->transactionRepository->calculateTotalBalanceByMonthYearAndCurrency($month, $year, $currency);
        
        // Renderizar la vista con los datos
        return $this->render('transaction/index.html.twig', [
            'transactions' => $transactions,
            'total_balance' => $totalBalance,
            'all_years' => $allYears,
            'all_months' => $allMonths,
            'year' => $year,
            'month' => $month,
            'currency' => $currency,
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