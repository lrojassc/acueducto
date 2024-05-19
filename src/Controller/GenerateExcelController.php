<?php

namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class GenerateExcelController extends MainController
{
    /**
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @param Security $security
     */
    public function __construct(EntityManagerInterface $entityManager, ValidatorInterface $validator, Security $security)
    {
        parent::__construct($entityManager, $validator, $security);
    }

    /**
     * Generar un reporte en excel con los filtros aplicados
     *
     * @param array $payments
     *
     * @return BinaryFileResponse
     */
    public function generatePaymentReport(array $payments): BinaryFileResponse
    {
        // Crear un nuevo objeto Spreadsheet
        $spreadsheet = new Spreadsheet();

        // Configurar el contenido del archivo Excel
        $sheet = $spreadsheet->getActiveSheet();

        // Definir encabezado de las columnas
        $sheet->setCellValue('A1', 'No. Pago');
        $sheet->getStyle('A1')->getFont()->setBold(true);
        $sheet->setCellValue('B1', 'Usuario');
        $sheet->getStyle('B1')->getFont()->setBold(true);
        $sheet->setCellValue('C1', 'Servicio');
        $sheet->getStyle('C1')->getFont()->setBold(true);
        $sheet->setCellValue('D1', 'No. Factura');
        $sheet->getStyle('D1')->getFont()->setBold(true);
        $sheet->setCellValue('E1', 'Mes Pagado');
        $sheet->getStyle('E1')->getFont()->setBold(true);
        $sheet->setCellValue('F1', 'Fecha de Pago');
        $sheet->getStyle('F1')->getFont()->setBold(true);
        $sheet->setCellValue('G1', 'Valor Pagado');
        $sheet->getStyle('G1')->getFont()->setBold(true);

        $count = 1;
        $total_payment = 0;
        // Asigar valor a la columa correspondiente
        foreach ($payments as $payment) {
            $count++;
            $sheet->setCellValue('A' . $count, $payment->getId());
            $sheet->setCellValue('B' . $count, $payment->getInvoice()->getUser()->getName());
            $sheet->setCellValue('C' . $count, $payment->getInvoice()->getSubscription()->getService());
            $sheet->setCellValue('D' . $count, $payment->getInvoice()->getId());
            $sheet->setCellValue('E' . $count, $payment->getMonthInvoiced());
            $sheet->setCellValue('F' . $count, $payment->getCreatedAt());
            $sheet->setCellValue('G' . $count, $payment->getValue());

            $total_payment = $total_payment + $payment->getValue();
        }

        //Asignar reporte de cantidad al ginal
        $sheet->setCellValue('A' . $count + 2, 'Cantidad De Pagos Realizados');
        $sheet->setCellValue('B' . $count + 2, count($payments));
        $sheet->setCellValue('A' . $count + 3, 'Total Pagos Realizados');
        $sheet->setCellValue('B' . $count + 3, $total_payment);

        // Crear un objeto Writer para guardar el archivo
        $writer = new Xlsx($spreadsheet);
        // Guardar el archivo en una ubicación temporal
        $tempFilePath = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFilePath);
        // Crear una respuesta binaria para devolver el archivo al cliente
        $response = new BinaryFileResponse($tempFilePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'Reporte_Pagos.xlsx'
        );
        return $response;
    }
}
