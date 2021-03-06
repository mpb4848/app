<?php
/**
 * This file is part of phpUnderControl.
 *
 * PHP Version 5.2.0
 *
 * Copyright (c) 2007-2010, Manuel Pichler <mapi@phpundercontrol.org>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name of Manuel Pichler nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * @category  QualityAssurance
 * @package   Data
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   SVN: $Id$
 * @link      http://www.phpundercontrol.org/
 */

/**
 * This class represents a single build.xml file.
 *
 * @category  QualityAssurance
 * @package   Data
 * @author    Manuel Pichler <mapi@phpundercontrol.org>
 * @copyright 2007-2010 Manuel Pichler. All rights reserved.
 * @license   http://www.opensource.org/licenses/bsd-license.php  BSD License
 * @version   Release: 0.6.1beta1
 * @link      http://www.phpundercontrol.org/
 *
 * @property-read string $projectName The build project name.
 * @property-read string $fileName    The build.xml file name.
 */
class phpucBuildFile extends DOMDocument
{
    /**
     * Magic properties for the build target.
     *
     * @var array(string=>array)
     */
    protected $properties = array(
        'projectName'  =>  null,
        'fileName'     =>  null,
    );

    /**
     * List of build file targets.
     *
     * @var array(phpucBuildTarget)
     */
    protected $targets = array();

    /**
     * The ctor takes the build file name and the project name as arguments.
     *
     * @param string $fileName    The build file name.
     * @param string $projectName An optional project name.
     */
    public function __construct( $fileName, $projectName = null )
    {
        parent::__construct( '1.0', 'UTF-8' );

        $this->properties['fileName']    = $fileName;
        $this->properties['projectName'] = $projectName;

        $this->formatOutput       = true;
        $this->preserveWhiteSpace = false;

        if ( file_exists( $fileName ) )
        {
            $this->load( $fileName );
            $this->properties['projectName'] 
                = $this->documentElement->getAttribute( 'name' );
        }
        else
        {
            $this->initBuildFile();
        }
    }

    /**
     * Magic property isset method.
     *
     * @param string $name The property name.
     *
     * @return boolean
     * @ignore
     */
    public function __isset( $name )
    {
        return array_key_exists( $name, $this->properties );
    }

    /**
     * Magic property getter method.
     *
     * @param string $name The property name.
     *
     * @return mixed
     * @throws OutOfRangeException If the requested property doesn't exist or
     *         is writeonly.
     * @ignore
     */
    public function __get( $name )
    {
        if ( array_key_exists( $name, $this->properties ) )
        {
            return $this->properties[$name];
        }
        throw new OutOfRangeException(
            sprintf( 'Unknown or writeonly property $%s.', $name )
        );
    }

    /**
     * Magic property setter method.
     *
     * @param string $name  The property name.
     * @param mixed  $value The property value.
     *
     * @return void
     * @throws OutOfRangeException If the requested property doesn't exist or
     *         is readonly.
     * @throws InvalidArgumentException If the given value has an unexpected
     *         format or an invalid data type.
     * @ignore
     */
    public function __set( $name, $value )
    {
        throw new OutOfRangeException(
            sprintf( 'Unknown or readonly property $%s.', $name )
        );
    }

    /**
     * Factory method for build targets.
     *
     * @param string $targetName The target identifier.
     *
     * @return phpucBuildTarget
     */
    public function createBuildTarget( $targetName )
    {
        $target = new phpucBuildTarget( $this, $targetName );

        $this->targets[] = $target;

        return $target;
    }

    /**
     * Processes target dependencies
     *
     * @see phpucBuildTarget::dependOn()
     *
     * @return void
     */
    protected function processTargetDependencies()
    {
        $xpath = new DOMXPath( $this );
        foreach ( $this->targets as $target )
        {
            if ( count( $target->depends ) > 0 )
            {
                $expr = "/project/target[@name='{$target->targetName}']";
                $elem = $xpath->query( $expr )->item( 0 );
                $elem->setAttribute( 'depends', implode( ',', $target->depends ) );
            }
        }
        unset($xpath);
    }

    /**
     * Writes changes to the build file.
     *
     * @return void
     */
    public function store()
    {
        foreach ( $this->targets as $target )
        {
            $target->buildXml();
        }

        $this->processTargetDependencies();

        $this->save( $this->fileName );
    }

    /**
     * Creates the base object structure for a new build file.
     *
     * @return void
     */
    protected function initBuildFile()
    {
        $project = $this->appendChild( $this->createElement( 'project' ) );
        $project->setAttribute( 'name', $this->projectName );
        $project->setAttribute( 'default', 'build' );
        $project->setAttribute( 'basedir', '.' );

        $build = $project->appendChild( $this->createElement( 'target' ) );
        $build->setAttribute( 'name', 'build' );
        $build->setAttribute( 'depends', '' );
    }
}