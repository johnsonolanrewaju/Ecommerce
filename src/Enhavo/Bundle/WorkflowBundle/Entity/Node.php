<?php

namespace Enhavo\Bundle\WorkflowBundle\Entity;

/**
 * Node
 */
class Node
{
    /**
     * @var integer
     */
    private $id;

    /**
     * @var string
     */
    private $node_name;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $transitions;

    /**
     * @var \Enhavo\Bundle\WorkflowBundle\Entity\Workflow
     */
    private $workflow;

    /**
     * @var boolean
     */
    private $end;

    /**
     * @var boolean
     */
    private $start;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->transitions = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set nodeName
     *
     * @param string $nodeName
     *
     * @return Node
     */
    public function setNodeName($nodeName)
    {
        $this->node_name = $nodeName;

        return $this;
    }

    /**
     * Get nodeName
     *
     * @return string
     */
    public function getNodeName()
    {
        return $this->node_name;
    }

    /**
     * Add transition
     *
     * @param \Enhavo\Bundle\WorkflowBundle\Entity\Transition $transition
     *
     * @return Node
     */
    public function addTransition(\Enhavo\Bundle\WorkflowBundle\Entity\Transition $transition)
    {
        $this->transitions[] = $transition;

        return $this;
    }

    /**
     * Remove transition
     *
     * @param \Enhavo\Bundle\WorkflowBundle\Entity\Transition $transition
     */
    public function removeTransition(\Enhavo\Bundle\WorkflowBundle\Entity\Transition $transition)
    {
        $this->transitions->removeElement($transition);
    }

    /**
     * Get transitions
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTransitions()
    {
        return $this->transitions;
    }

    /**
     * Set workflow
     *
     * @param \Enhavo\Bundle\WorkflowBundle\Entity\Workflow $workflow
     *
     * @return Node
     */
    public function setWorkflow(\Enhavo\Bundle\WorkflowBundle\Entity\Workflow $workflow = null)
    {
        $this->workflow = $workflow;

        return $this;
    }

    /**
     * Get workflow
     *
     * @return \Enhavo\Bundle\WorkflowBundle\Entity\Workflow
     */
    public function getWorkflow()
    {
        return $this->workflow;
    }

    /**
     * Set end
     *
     * @param boolean $end
     *
     * @return Node
     */
    public function setEnd($end)
    {
        $this->end = $end;

        return $this;
    }

    /**
     * Get end
     *
     * @return boolean
     */
    public function getEnd()
    {
        return $this->end;
    }

    /**
     * Set start
     *
     * @param boolean $start
     *
     * @return Node
     */
    public function setStart($start)
    {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return boolean
     */
    public function getStart()
    {
        return $this->start;
    }
}
