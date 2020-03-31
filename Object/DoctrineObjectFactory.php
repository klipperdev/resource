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
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $em The entity manager
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function create(string $classname, array $options = [])
    {
        return $this->em->getClassMetadata($classname)->newInstance();
    }
}
