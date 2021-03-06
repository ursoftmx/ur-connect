<?php
 
class WP_REST_Articles_Controller extends WP_REST_Controller {
  
  /**
   * Register the routes for the objects of the controller.
   */
  public function register_routes() {

    $namespace = 'api/v1/';
    $base = 'articles';
    
    register_rest_route( $namespace, '/' . $base, array(
      array(
        'methods'             => WP_REST_Server::CREATABLE,
        'callback'            => array( $this, 'create_item' ),
        'permission_callback' => array( $this, 'create_item_permissions_check' ),
        'args'                => $this->get_endpoint_args_for_item_schema( true ),
      ),
    ) );

  }
 
  /**
   * Create one item from the collection
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|WP_REST_Request
   */
  public function create_item( $request ) {


    $post_params = $request->get_params(); 
    $result = [];  

    $data =  json_decode ( stripslashes($post_params['data'])  , true);

    $url = 'https://tiendaenlinea.ursoft.com.mx/wp-json/wc/v2/products';
    $headers = array (
        'Content-Type' => 'application/json; charset=utf-8',
        'Authorization' => 'Basic Y2tfNjljODIxNGQ2OTM2ODk0NDNjNTVlMjE0MGQ3OTE2ZjVkOTEyYTUwMzpjc19mYzAwZjI2ODAzMTU0Njg5MzI5ZmQ0MTVkZjY4MDE5ODJiZDZmOGI5',        
    );

    $response = wp_remote_post( $url, array (
        'method'  => 'POST',
        'headers' => $headers,
        'body'    =>  json_encode($data),
        'data_format' => 'body',
    ) );

    $status = array(
        'success'=> true,
        'message' => 'message',
        'code' => 'code'
      );

      if ( defined('WP_DEBUG') && true === WP_DEBUG ){
        $result = true;
      }else{

        $json_response = json_decode ( wp_remote_retrieve_body( $response ) );
        if ( $json_response->code ){
            // errr
            $status = [
                'success' => false,
                'message' => 'Has been ocurred an error'
            ];
        
            $result = "ya existe";

        } else {

            // success
            $status = [
                'success' => true,
                'message' => 'category has been added succesfully'
            ];;
        
            $result = $json_response;

        }

      }
           
      $data = $this->prepare_response_for_collecction( $status, $result );
  
      return new WP_REST_Response( $data, 200 );     
  }

  public function fetchResponse( $status ){

    $success =  $status->success;
    
    if ( $status->success && strpos( $status->operation, '-') !== false ){
      $success = false;
    }
    
    return array(
      'success' =>  $success,
      'message' => $status->message,
      'code' => $status->code
    );

  }

  public function validToken ( $token ) {
    $api_token  =  get_option('api_token');    

    if ( !$token )
      return false;

    if ( strcmp($token, $api_token) !== 0 )
      return false;

    return true;
  }
  
  /**
   * Check if a given request has access to create items
   *
   * @param WP_REST_Request $request Full data about the request.
   * @return WP_Error|bool
   */
  public function create_item_permissions_check( $request ) {
    return true;
  }
 
 
  /**
   * Prepare the item for create or update operation
   *
   * @param WP_REST_Request $request Request object
   * @return WP_Error|object $prepared_item
   */
  protected function prepare_item_for_database( $request ) {
    return array();
  }
 
  /**
   * Prepare the item for the REST response
   *
   * @param mixed $item WordPress representation of the item.
   * @param WP_REST_Request $request Request object.
   * @return mixed
   */
  public function prepare_item_for_response( $item, $request ) {
    return array();
  }

  /**
   * Prepare the response for the REST response
   *
   * @param mixed $item WordPress representation of the item.
   * @param WP_REST_Request $request Request object.
   * @return mixed
   */
  public function prepare_response_for_collecction( $status, $response  ) {

    $success = $status['success'];
    unset( $status['success']);

    return array(
      'success' => $success,
      'data' => $response,
      'status' => $status
    );
  }
 
}

add_action('rest_api_init', function () {           
  $latest_posts_controller = new WP_REST_Articles_Controller();
  $latest_posts_controller->register_routes();
});