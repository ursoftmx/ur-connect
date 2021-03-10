<?php

class WP_REST_Orders_Controller extends WP_REST_Controller
{

  /**
   * Register the routes for the objects of the controller.
   */
  public function register_routes()
  {

    $namespace = 'api/v1';
    $base = 'orders';

    register_rest_route($namespace, '/' . $base, array(
      array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => array($this, 'list_items'),
        'permission_callback' => array($this, 'create_item_permissions_check'),
        'args'                => $this->get_endpoint_args_for_item_schema(true),
      ),
    ));

    register_rest_route($namespace, '/' . $base . '/(?P<id>\d+)', array(
      array(
        'methods'             => WP_REST_Server::READABLE,
        'callback'            => array($this, 'list_item'),
        'permission_callback' => array($this, 'create_item_permissions_check'),
        'args'                => $this->get_endpoint_args_for_item_schema(true),
      ),
      array(
        'methods'             => WP_REST_Server::EDITABLE,
        'callback'            => array($this, 'update_item'),
        'permission_callback' => array($this, 'create_item_permissions_check'),
        'args'                => $this->get_endpoint_args_for_item_schema(true),
      ),
    ));
  }

  /**
   * List items from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function list_items($request)
  {
    $status = $request->get_param('status');
    $authorization = $request->get_header('authorization');
    $host =  $request->get_header('host');
    $segment = 'orders' . ($status ? "?status=$status" : '');
    $result = [];

    $url = "https://$host/wp-json/wc/v2/$segment";

    $headers = array(
      'Content-Type' => 'application/json; charset=utf-8',
      'Authorization' => $authorization,
    );

    $response = wp_remote_get($url, array(
      'method'  => WP_REST_Server::READABLE,
      'headers' => $headers
    ));

    $status = array(
      'success' => true,
      'message' => 'message',
      'code' => 'code'
    );

    if (is_wp_error($response)) {
      // errr
      $status = [
        'debug' => $data,
        'success' => false,
        'message' => $response->get_error_message()
      ];

      $result = null;
    } else {

      // success
      $status = [
        'success' => true,
        'message' => 'Getting orders'
      ];

      $res = json_decode(wp_remote_retrieve_body($response));

      if (isset($res->code)) {

        $status = [
          'success' => false,
          'message' => $res->data->params->status
        ];

        $result = null;
      } else {


        $items =  array_map(function ($e) {
          return ['order_id' => $e->id];
        }, $res);

        $result = [
          'records' => $items,
          'total' => count($items)
        ];
      }
    }

    $data = $this->prepare_response_for_collecction($status, $result);

    return new WP_REST_Response($data, 200);
  }

  /**
   * Get item by Id from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function list_item($request)
  {
    $id = $request->get_param('id');
    $authorization = $request->get_header('authorization');
    $host =  $request->get_header('host');
    $segment = "orders/$id";
    $result = [];

    $url = "https://$host/wp-json/wc/v2/$segment";

    $headers = array(
      'Content-Type' => 'application/json; charset=utf-8',
      'Authorization' => $authorization,
    );

    $response = wp_remote_get($url, array(
      'method'  => WP_REST_Server::READABLE,
      'headers' => $headers
    ));

    $status = array(
      'success' => true,
      'message' => 'message',
      'code' => 'code'
    );

    if (is_wp_error($response)) {
      // errr
      $status = [
        'debug' => $data,
        'success' => false,
        'message' => $response->get_error_message()
      ];

      $result = null;
    } else {

      // success
      $status = [
        'success' => true,
        'message' => 'Getting orders'
      ];

      $res = json_decode(wp_remote_retrieve_body($response));

      if (isset($res->code)) {

        $status = [
          'success' => false,
          'message' => $res->data->params->status
        ];

        $result = null;
      } else {

        $result = $res;
      }
    }

    $data = $this->prepare_response_for_collecction($status, $result);

    return new WP_REST_Response($data, 200);
  }

  /**
   * Update one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function update_item($request)
  {
    $authorization = $request->get_header('authorization');
    $host =  $request->get_header('host');
    $id = $request->get_param('id');

    $segment = "orders/$id";
    $result = [];

    $data = json_encode([
      'status' => $request->get_param('status')
    ]);
    $url = "https://$host/wp-json/wc/v2/$segment";

    $headers = array(
      'Content-Type' => 'application/json; charset=utf-8',
      'Authorization' => $authorization,
    );

    $response = wp_remote_request($url, array(
      'method'  => 'PUT',
      'headers' => $headers,
      'body'    => $data,
      'data_format' => 'body',
    ));

    $status = array(
      'success' => true,
      'message' => 'message',
      'code' => 'code'
    );

    if (is_wp_error($response)) {
      // errr
      $status = [
        'debug' => $data,
        'success' => false,
        'message' => $response->get_error_message()
      ];

      $result = null;
    } else {

      // success
      $status = [
        'success' => true,
        'message' => 'Updating order status'
      ];

      $res = json_decode(wp_remote_retrieve_body($response));

      if (isset($res->code)) {

        $status = [
          'success' => false,
          'message' => $res->data->params->status
        ];

        $result = null;
      } else {

        $result = $res;
      }
    }

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
  $latest_posts_controller = new WP_REST_Orders_Controller();
  $latest_posts_controller->register_routes();
});
