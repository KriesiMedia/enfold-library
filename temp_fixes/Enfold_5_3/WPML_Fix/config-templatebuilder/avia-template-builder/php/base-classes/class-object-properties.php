<?php
namespace aviaBuilder\base;

/**
 * Base Class to ensure that also protected properties can be accessed. This is to provide a fallback
 * to older versions which only had "default" public propertiers.
 *
 * Identic to class object_properties in framework
 *
 * In case defined( 'WP_DEBUG' ) errorhandling and message are used to show the problem.
 *
 * @added_by Günter
 * @since 5.3
 */
if( ! defined( 'ABSPATH' ) ) { exit; }

if( ! class_exists( __NAMESPACE__ . '\object_properties' ) )
{
	abstract class object_properties
	{
		/**
		 * Is set to true in createProperty()
		 *
		 * @since 5.3.1
		 * @var boolean
		 */
		private $forceset;


		/**
		 * Access modifiers changed from public to protected for most properties
		 * Fallback to allow access for backwards comp.
		 *
		 * @since 5.3
		 * @param string $name
		 */
		public function __get( $name )
		{
			if( ! property_exists( $this, $name ) )
			{
				if( defined( 'WP_DEBUG' ) && WP_DEBUG )
				{
					$message = sprintf( __( 'Trying to read non existing property in class %s: %s', 'avia_framework' ), get_class( $this ), $name );
//					throw new \InvalidArgumentException ( $message );
					_deprecated_argument( 'Class ' . get_class( $this ), '5.3', $message );
				}

				return null;
			}

			$message = sprintf( __( 'Trying to access protected/private property: %s::%s - will become unavailable in a future release. Check for a get method or a filter.', 'avia_framework' ), get_class( $this ), $name );
			_deprecated_argument( 'Class ' . get_class( $this ), '5.3', $message );

			return $this->{$name};
		}

		/**
		 * Access modifiers changed from public to protected for most properties
		 * Fallback to allow access for backwards comp.
		 *
		 * @since 5.3
		 * @param string $name
		 * @param mixed $value
		 */
		public function __set( $name, $value )
		{
			if( true === $this->forceset )
			{
				$this->{$name} = $value;
				return;
			}

			if( ! property_exists( $this, $name ) )
			{
				if( defined( 'WP_DEBUG' ) && WP_DEBUG )
				{
					$message = sprintf( __( 'Trying to set non existing property in class %s: %s - please consider to define all properties before using', 'avia_framework' ), get_class( $this ), $name );
//					throw new \InvalidArgumentException( $message );
					_deprecated_argument( 'Class ' . get_class( $this ), '5.3', $message );
				}

				$this->{$name} = $value;
				return;
			}

			//	when property was unset() prior this is also called
			$rp = new \ReflectionProperty( $this, $name );

			if( ! $rp->isPublic() )
			{
				$message = sprintf( __( 'Trying to set protected/private property: %s::%s - will become unavailable in a future release. Check for a set method or a filter.', 'avia_framework' ), get_class( $this ), $name );
				_deprecated_argument( 'Class ' . get_class( $this ), '5.3', $message );
			}

			$this->{$name} = $value;
		}

		/**
		 * Access modifiers changed from public to protected for most properties
		 * Fallback to allow access for backwards comp.
		 *
		 * @since 5.3
		 * @param string $name
		 * @param mixed $value
		 */
		public function __isset( $name )
		{
			if( ! property_exists( $this, $name ) )
			{
				return false;
			}

			return empty( $this->{$name} );
		}

		/**
		 * Access modifiers changed from public to protected for most properties
		 * Fallback to allow access for backwards comp.
		 *
		 * @since 5.3
		 * @param string $name
		 * @param mixed $value
		 */
		public function __unset( $name )
		{
			if( property_exists( $this, $name ) )
			{
				unset( $this->{$name} );
			}
		}

		/**
		 * Adds a public property to the class
		 *
		 * @since 5.3.1
		 * @param string $name
		 * @param mixed $value
		 */
		public function createProperty( $name, $value )
		{
			$this->forceset = true;

			$this->{$name} = $value;

			$this->forceset = false;
		}
	}

}
