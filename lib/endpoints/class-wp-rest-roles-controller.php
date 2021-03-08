<?php

class WP_REST_Roles_Controller extends WP_REST_Controller
{

  /**
   * Register the routes for the objects of the controller.
   */
  public function register_routes()
  {

    $namespace = 'api/v1';
    $base = 'roles';

    register_rest_route($namespace, '/' . $base, array(
      array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => array($this, 'get_items'),
        'permission_callback' => array($this, 'create_item_permissions_check'),
        'args'                => $this->get_endpoint_args_for_item_schema(true),
      ),
      array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => array($this, 'create_item'),
        'permission_callback' => array($this, 'create_item_permissions_check'),
        'args'                => $this->get_endpoint_args_for_item_schema(true),
      ),
    ));
  }

  /**
   * Updates roles
   *
   * @param [string] $value
   * @return boolen
   */
  public function update_roles($value)
  {
    global $wpdb;

    return $wpdb->update(
      "{$wpdb->prefix}options",
      [
        'option_value' => $value
      ],
      [
        'option_name' =>  "{$wpdb->prefix}user_roles"
      ]
    );
  }


  /**
   * Gets the roles
   *
   * @return string serialized
   */
  public function get_roles()
  {
    global $wpdb;

    $query = $wpdb->get_row(
      $wpdb->prepare(
        " SELECT * FROM {$wpdb->prefix}options WHERE `option_name` like '%_user_roles' "
      )
    );

    return unserialize($query->option_value);
  }

  /**
   * Create one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function create_item($request)
  {
    $elements = $this->get_roles();
    $post_params = $request->get_params();
    $name = $post_params['name'];
    $index_name =  strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '_', $name)));

    $status = [
      'success' => true,
      'message' => 'Role has been added'
    ];

    if (!isset($post_params['name']) ||  $post_params['name'] == '' || in_array($index_name, array_keys($elements))) {
      $status = [
        'success' => false,
        'message' => 'name role has been required or already exists'
      ];

      $data = $this->prepare_response_for_collecction($status, false);

      return new WP_REST_Response($data, 200);
    }

    $elements[$index_name] = [
      'name ' => $name,
      'capabilities' => []
    ];

    $serialize = serialize($elements);
    $updated =  $this->update_roles($serialize);

    if (!$updated) {
      $status = [
        'success' => false,
        'message' => 'name role has not been added'
      ];
    }

    $data = $this->prepare_response_for_collecction($status, $updated);

    return new WP_REST_Response($data, 200);
  }

  /**
   * Get all user Roles as array
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function get_items($request)
  {
    $items = [];
    $elements = $this->get_roles();

    foreach ($elements as $element) {
      $items[] = $element;
    }

    $status = array(
      'success' => true,
      'message' => 'Getting roles'
    );

    $result = [
      'records' => $items,
      'total' => count($items)
    ];

    $data = $this->prepare_response_for_collecction($status, $result);

    return new WP_REST_Response($data, 200);
  }

  /**
   * Check if a given request has access to create items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function create_item_permissions_check($request)
  {
    return true;
  }


  /**
   * Prepare the item for create or update operation
   *
   * @param WP_REST_Request $request Request object
   * @return WP_Error|object $prepared_item
   */
  protected function prepare_item_for_database($request)
  {
    return array();
  }

  /**
   * Prepare the item for the REST response
   *
   * @param mixed $item WordPress representation of the item.
   * @param WP_REST_Request $request Request object.
   * @return mixed
   */
  public function prepare_item_for_response($item, $request)
  {
    return array();
  }

  /**
   * Prepare the response for the REST response
   *
   * @param mixed $item WordPress representation of the item.
   * @param WP_REST_Request $request Request object.
   * @return mixed
   */
  public function prepare_response_for_collecction($status, $response)
  {

    $success = $status['success'];
    unset($status['success']);

    return array(
      'success' => $success,
      'data' => $response,
      'status' => $status
    );
  }
}

add_action('rest_api_init', function () {
  $latest_posts_controller = new WP_REST_Roles_Controller();
  $latest_posts_controller->register_routes();
});
