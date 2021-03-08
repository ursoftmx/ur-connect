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
    ));
  }

  /**
   * Create one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function get_items($request)
  {
    global $wpdb;

    $query = $wpdb->get_row(
      $wpdb->prepare(
        " SELECT * FROM {$wpdb->prefix}options WHERE `option_name` like '%_user_roles' "
      )
    );

    $result = [];
    $elements = unserialize($query->option_value);

    foreach ($elements as $element) {
      $result[] = $element;
    }

    $status = array(
      'success' => true,
      'message' => 'Getting roles'
    );

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
      'data' => [
        'records' => $response,
        'total' => count($response)
      ],
      'status' => $status
    );
  }
}

add_action('rest_api_init', function () {
  $latest_posts_controller = new WP_REST_Roles_Controller();
  $latest_posts_controller->register_routes();
});
