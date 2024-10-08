import React, { Component } from 'react';
import {
  Alert,
  Row,
  Col,
  Card,
  CardHeader,
  CardBody,
  Table,
  Button,
  Modal, ModalHeader, ModalBody, ModalFooter,
  Form,
  FormGroup,
  FormText,
  Label,
  Input,
  Badge

} from 'reactstrap';
import axios from 'axios';
// import Select from 'react-select';
import Paginations from "react-js-pagination";

import Can from '../../../Can';



class Description extends Component {
  constructor(props) {
    super(props)
    this.state = {
      descriptions: [],
      

      add_form: false,
      id: '',
      user_id: window.current_user_id,
      internship_id: this.props.internship_id,
      description: '',

      is_add_process_type: true,
      save_button_is_disabled: true,

      has_alert_hidden: true,
      alert_type: 'danger',
      alert_message: '',

      active_page:1,
      items_count_per_page:1,
      total_items_count:1,
      current_index:'',

      search_loading: false,
      search_message: '',
      search_alert_type: 'primary',
      search_value:''
      
    }
    this.toggle_add_form = this.toggle_add_form.bind(this);
    this.handle_input_change = this.handle_input_change.bind(this);
    this.save_item = this.save_item.bind(this);

    this.get_data = this.get_data.bind(this);
    this.check_inputs = this.check_inputs.bind(this);
    this.delete_item = this.delete_item.bind(this);
    this.disable_buttons = this.disable_buttons.bind(this);
    this.enable_button = this.enable_button.bind(this);
    this.handle_page_change = this.handle_page_change.bind(this);
    this.handle_search_input_change = this.handle_search_input_change.bind(this);
    
    this.store_data_to_state = this.store_data_to_state.bind(this);
  
  }


  disable_buttons(){
    this.setState(
      {
        save_button_is_disabled: true,

      }
    ) 
  }
  enable_button(){

      this.setState(
        {
          save_button_is_disabled: false,

        })
    
  }

  check_inputs() {
    if (this.state.description == '' ) {
      console.log('Incomplete form values!')
      this.disable_buttons()
    }else{
      this.enable_button()
    }
  }

  delete_item(i) {
    var self = this;
    axios.delete('api/descriptions/' + this.state.descriptions[i].id)
      .then(function (response) {
        console.log(response);
        self.get_data()
        self.setState({
          alert_message: 'Delete Successful.',
          alert_type: 'success',
          has_alert_hidden: false,
        })

      })
      .catch(function (error) {
        console.log(error);
        alert('Deletion Failed. Contact your System Administrator')
      });
  }


  save_item(e) {
    e.preventDefault();
    
    var self = this;
    let payload = {
      internship_id: this.props.internship_id,
      description: this.state.description,

    }
    axios.post('/api/descriptions', payload)
      .then(function (response) {
        console.log(response);
        self.get_data()
        self.toggle_add_form();
      })
      .catch(function (error) {
        console.log(error.response.status);
        if (error.response.data.message.includes('Description already')) {
          self.setState({
            alert_message: error.response.data.message,
            alert_type: 'danger',
            has_alert_hidden: false,
          })
        }else{
          alert('Save Failed. Contact your System Administrator')
        }
        

      });

  }
  handle_search_input_change(e) {
    // clear alert status
    var self = this;
    self.setState({
     search_message: '',
     search_alert_type: 'primary',
     has_alert_hidden: true,
     search_value: e.target.value
   })
   var value = 'xxxxxxxxxx';
   if(e.target.value){
    value = e.target.value
   
   axios.get('/api/descriptions/search/'+value+'/internship/'+this.props.internship_id)
      .then(function (response) {
        console.log(response);
        self.store_data_to_state(response.data)
      })
      .catch(function (error) {
        console.log(error);
        alert('Search Failed. Contact your System Administrator.')
      });
  
    }else{
      this.get_data()
    }
  }

  handle_input_change(e) {
     // clear alert status
    this.setState({
      alert_message: '',
      alert_type: 'primary',
      has_alert_hidden: true,
    })

    this.setState({ [e.target.name]: e.target.value });
    
    if (e.target.value == '') {
      this.disable_buttons();
      setTimeout(() => {
      
        this.check_inputs(e.target.name, e.target.value);
      }, 500);
    }else{
      this.check_inputs(e.target.name, e.target.value);
    }
    


  }

  toggle_add_form() {
    this.check_inputs();
    this.setState({
      id: '',
      internship_id: '',
      description: '',
      
      add_form: !this.state.add_form,
      is_add_process_type: true,

      alert_message: '',
      alert_type: 'primary',
      has_alert_hidden: true,
    });

  }
  

  store_data_to_state(data) {
    // console.log(data)
    this.setState({ 
      descriptions: data.data,

      active_page: data.meta.current_page,
      items_count_per_page:data.meta.per_page,
      total_items_count:data.meta.total,
     })
  }

  get_data(page_number) {
    var self = this;
    if (this.props.internship_id) {
      axios.get('/api/descriptions/internship/'+this.props.internship_id)
      .then(res => {
        self.store_data_to_state(res.data)
      }).catch(err => {
        console.log(err)
        alert('Server disconnected.')
      })
    }
    
  }

  componentDidUpdate(prevProps, prevState) {
    const { internship_id } = this.props;
    if (prevProps.internship_id !== internship_id) this.get_data();

  }

  handle_page_change(page_number) {
    console.log(`active page is ${page_number}`);
    this.get_data(page_number)
  }

  render() {
    return (
      
        <Row hidden={this.props.is_hidden}>
          <Col xs="12" lg="12">
            <Card>
              <CardHeader>
                <Row>
                <Col xs="4" lg="4">
                  
                  <i className="fa fa-tasks"></i> Descriptions
                  </Col>
                  <Col xs="4" lg="4">
                  
                  <Input
                          type="text"
                          value={this.state.search_value}
                          
                          name="search"
                          placeholder="Search..."
                          onChange={this.handle_search_input_change}
                          />
                    </Col>
                    
                
                
                  <Col xs="4" lg="4">
                        
                        <Can
                                role={user.role}
                                perform="job-description:add"
                                yes={() => <Button hidden={this.props.is_approved} className="float-lg-right" color="primary" onClick={this.toggle_add_form}><i className="fa fa-plus-circle"></i> Add</Button>
                              }
                                no={() => <div></div>}
                                />
                        
                        
                  </Col>
                </Row>
              </CardHeader>
              <CardBody>
                <Modal isOpen={this.state.add_form} toggle={this.toggle_add_form}
                  className={'modal-primary ' + this.props.className}>
                  <ModalHeader toggle={this.toggle_add_form}>Add New Job Description</ModalHeader>
                  <ModalBody>

                    <Row>
                      <Col xs="12" md="12">
                        <Card>

                          <CardBody>
                            <Form action="" method="post" encType="multipart/form-data" className="form-horizontal">

                            
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="description">Job Description</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input value={this.state.description} onChange={this.handle_input_change} type="text" id="description" name="description" placeholder="Text" />
                                  <FormText color="muted">Please enter the job description</FormText>
                                </Col>
                              </FormGroup>
                              
                              
                              <Alert hidden={this.state.has_alert_hidden} color={this.state.alert_type}>
                                    {this.state.alert_message}
                                  </Alert>
                            </Form>

                          </CardBody>
                        </Card>
                      </Col>
                    </Row>

                  </ModalBody>
                  <ModalFooter>
                    <Button type='submit' disabled={this.state.save_button_is_disabled} color="primary" onClick={this.save_item}><i className="fa fa-save"></i> Save</Button>{' '}
                    <Button color="secondary" onClick={this.toggle_add_form}>Cancel</Button>
                  </ModalFooter>
                </Modal>
                
                <Alert hidden={this.state.has_alert_hidden} color={this.state.alert_type}>
                                    {this.state.alert_message}
                                  </Alert>
                <Table responsive>
                  <thead>
                    <tr>
                      <th>Job Description</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {this.state.descriptions.map((data, i) => {
                      return (
                        <tr key={i}>
                          <td>{data.description}</td>
                          <td>
                          <Can
                                role={user.role}
                                perform="job-description:delete"
                                yes={() => <Button hidden={this.props.is_approved} size="sm" onClick={(e) => { if (window.confirm('Are you sure you wish to delete this item?')) this.delete_item(i) }} color="danger"><i className="fa fa-trash"></i></Button>
                              }
                                no={() => <div></div>}
                                />
                            
                          </td>
                        </tr>
                      )
                    })}


                  </tbody>
                </Table>
                <Paginations
                    activePage={this.state.active_page}
                    itemsCountPerPage={this.state.items_count_per_page}
                    totalItemsCount={this.state.total_items_count}
                    pageRangeDisplayed={5}
                    onChange={this.handle_page_change}
                />
                
              </CardBody>
            </Card>
          </Col>
          </Row>
      

    )
  }
}

export default Description;
