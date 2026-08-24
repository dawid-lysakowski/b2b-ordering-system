<?php

namespace App\Service;

use App\Entity\CustomerOrder;
use App\Repository\CustomerOrderRepository;

class OrderNumberGenerator
{
    public function __construct(
        private CustomerOrderRepository $orderRepository,
    ) {
    }

    public function generate(): string
    {
        $year = date('Y');

        $lastOrder = $this->orderRepository->createQueryBuilder('o')
            ->where('o.orderNumber LIKE :prefix')
            ->setParameter('prefix', $year . '/%')
            ->orderBy('o.orderNumber', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        $nextNumber = 1;

        if ($lastOrder instanceof CustomerOrder) {
            [, $number] = explode('/', $lastOrder->getOrderNumber());
            $nextNumber = ((int) $number) + 1;
        }

        return sprintf('%s/%06d', $year, $nextNumber);
    }
}