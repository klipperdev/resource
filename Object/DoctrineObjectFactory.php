<?php

/*
 * This file is part of the Klipper package.
 *
 * (c) François Pluchino <francois.pluchino@klipper.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Klipper\Component\Resource\Object;

use Doctrine\ORM\EntityManagerInterface;

/**
 * A doctrine object factory.
 *
 * @author François Pluchino <francois.pluchino@klipper.dev>
 */
class DoctrineObjectFactory implements ObjectFactoryInterface
{
    private EntityManagerInterface $em;

    /**
     * @param EntityManagerInterface $em The entity manager
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function create(string $classname, array $options = [])
    {
        return $this->em->getClassMetadata($classname)->newInstance();
    }
}
