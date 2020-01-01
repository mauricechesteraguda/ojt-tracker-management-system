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

} from 'reactstrap';
import axios from 'axios';
import Select from 'react-select';
import Paginations from "react-js-pagination";



class Internship extends Component {
  constructor(props) {
    super(props)
    this.state = {
      internships: [],

      add_form: false,
      id: '',
      user_id: '',
      company_id: '',
      start_date: '',
      representative: '',
      student_position: '',
      is_approved: 0,
      status: '',
      comment: '',
      updated_by: '',

      is_add_process_type: true,
      save_button_is_disabled: true,
      update_button_is_disabled: true,

      has_alert_hidden: true,
      alert_type: 'danger',
      alert_message: '',

      active_page:1,
      items_count_per_page:1,
      total_items_count:1,
      detail_modal: false,

      search_loading: false,
      search_message: '',
      search_alert_type: 'primary',
      search_value:''
      
    }
    this.toggle_add_form = this.toggle_add_form.bind(this);
    this.handle_input_change = this.handle_input_change.bind(this);
    this.save_item = this.save_item.bind(this);
    this.update_item = this.update_item.bind(this);
    this.get_data = this.get_data.bind(this);
    this.check_inputs = this.check_inputs.bind(this);
    this.delete_item = this.delete_item.bind(this);
    this.disable_buttons = this.disable_buttons.bind(this);
    this.handle_page_change = this.handle_page_change.bind(this);
    this.toggle_detail_modal = this.toggle_detail_modal.bind(this);
    this.handle_search_input_change = this.handle_search_input_change.bind(this);
  
  }

  disable_buttons(){
    this.setState(
      {
        save_button_is_disabled: true,
        update_button_is_disabled: true,
      }
    ) 
  }
  enable_button(){

    if (this.state.is_add_process_type) {
      this.setState(
        {
          save_button_is_disabled: false,
          update_button_is_disabled: true,
        }
      ) 
    }else{
      this.setState(
        {
          save_button_is_disabled: true,
          update_button_is_disabled: false,
        }
      ) 
    }
    
  }

  check_inputs() {

    if (this.state.user_id =='' || this.state.company_id == '' ) {
      console.log('Incomplete form values!')
      this.disable_buttons()
    }else{
      this.enable_button()
    }
  }

  delete_item(i) {
    var self = this;
    axios.delete('api/internships/' + this.state.internships[i].id)
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

  update_item(e) {
    e.preventDefault();

    var self = this;
    let payload = {
      user_id: this.state.user_id,
      company_id: this.state.company_id,
      start_date: this.state.start_date,
      representative: this.state.representative,
      student_position: this.state.student_position,
      is_approved: this.state.is_approved,
      status: this.state.status,
      comment: this.state.comment,
      updated_by: this.state.updated_by,
    }
    axios.post('/api/internships/' + this.state.id, payload)
      .then(function (response) {
        console.log(response);
        self.get_data()
        self.toggle_add_form()
        self.setState({
          alert_message: 'Update Successful.',
          alert_type: 'success',
          has_alert_hidden: false,
        })
      })
      .catch(function (error) {
        console.log(error);
        alert('Update Failed. Contact your System Administrator')
      });

  }

  load_item(i) {
    this.toggle_add_form();
    this.setState({
      id: this.state.internships[i].id,
      user_id: this.state.internships[i].user_id,
      company_id: this.state.internships[i].company_id,
      start_date: this.state.internships[i].start_date,
      representative: this.state.internships[i].representative,
      student_position: this.state.internships[i].student_position,
      is_approved: this.state.internships[i].is_approved,
      status: this.state.internships[i].status,
      comment: this.state.internships[i].comment,
      updated_by: this.state.internships[i].updated_by,

      update_button_is_disabled: true,

    })

    this.setState({ is_add_process_type: false });
  }

  save_item(e) {
    e.preventDefault();
    
    var self = this;
    let payload = {
      user_id: this.state.user_id,
      company_id: this.state.company_id,
      start_date: this.state.start_date,
      representative: this.state.representative,
      student_position: this.state.student_position,
      is_approved: this.state.is_approved,
      status: this.state.status,
      comment: this.state.comment,
      updated_by: this.state.updated_by,
    }
    axios.post('/api/internships', payload)
      .then(function (response) {
        console.log(response);
        self.get_data()
        self.toggle_add_form();
      })
      .catch(function (error) {
        console.log(error.response.status);
        if (error.response.data.message.includes('Internship already')) {
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
   
   axios.get('/api/internships/search/'+value)
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
    
    
    this.check_inputs(e.target.name, e.target.value);



  }

  toggle_add_form() {
    this.setState({
      id: '',
      user_id: '',
      company_id: '',
      start_date: '',
      representative: '',
      student_position: '',
      is_approved: 0,
      status: '',
      comment: '',
      updated_by: '',
      // selectedOptionCompany:{},

      update_button_is_disabled: true,
      add_form: !this.state.add_form,
      is_add_process_type: true,

      alert_message: '',
      alert_type: 'primary',
      has_alert_hidden: true,
    });

  }
  toggle_detail_modal(i=undefined){
    
    if (i >= 0 ) {
      this.setState({
        detail_modal: !this.state.detail_modal,
        id: this.state.internships[i].id,
        user_id: this.state.internships[i].user_id,
        company_id: this.state.internships[i].company_id,
        start_date: this.state.internships[i].start_date,
        representative: this.state.internships[i].representative,
        student_position: this.state.internships[i].student_position,
        is_approved: this.state.internships[i].is_approved,
        status: this.state.internships[i].status,
        comment: this.state.internships[i].comment,
        updated_by: this.state.internships[i].updated_by,
      });
    }else{
      this.setState({
        detail_modal: !this.state.detail_modal,
      });
    }
    
  }

  store_data_to_state(data) {
    // console.log(data)
    this.setState({ 
      internships: data.data,

      active_page: data.meta.current_page,
      items_count_per_page:data.meta.per_page,
      total_items_count:data.meta.total,
     })
  }

  get_data(page_number) {
    var self = this;
    axios.get('/api/internships/?page='+ page_number)
      .then(res => {
        self.store_data_to_state(res.data)
      }).catch(err => {
        console.log(err)
        alert('Server disconnected.')
      })
  }

  componentDidMount() {

    this.get_data()
    this.get_companies()

  }

  handle_page_change(page_number) {
    console.log(`active page is ${page_number}`);
    this.get_data(page_number)
  }

  render() {
    return (
      <div className="animated fadeIn">
        <Row>
          <Col xs="12" lg="12">
            <Card>
              <CardHeader>
                <Row>
                <Col xs="4" lg="4">
                  
                  <i className="fa fa-align-justify"></i> Internship
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
                        
                        <Button className="float-lg-right" color="primary" onClick={this.toggle_add_form}><i className="fa fa-plus-circle"></i> Add</Button>
                        
                  </Col>
                </Row>
              </CardHeader>
              <CardBody>
                <Modal isOpen={this.state.add_form} toggle={this.toggle_add_form}
                  className={'modal-primary ' + this.props.className}>
                  <ModalHeader toggle={this.toggle_add_form}>{this.state.is_add_process_type ? 'Add New' : 'Update'} Internship</ModalHeader>
                  <ModalBody>

                    <Row>
                      <Col xs="12" md="12">
                        <Card>

                          <CardBody>
                            <Form action="" method="post" encType="multipart/form-data" className="form-horizontal">

                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="name">Name</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input value={this.state.name} onChange={this.handle_input_change} type="text" id="name" name="name" placeholder="Text" />
                                  <FormText color="muted">Internship Name</FormText>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="country">Country</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input value={this.state.country} onChange={this.handle_input_change} type="text" id="country" name="country" placeholder="Text" />
                                  <FormText color="muted">Please write the country</FormText>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="city">City</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input  value={this.state.city} onChange={this.handle_input_change} type="text" name="city" id="city"/>
                                  
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="address">Address</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input  value={this.state.address} onChange={this.handle_input_change} type="text" id="address" name="address" placeholder="Text" />
                                  <FormText className="help-block">Please enter the address</FormText>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="location_map">Location Map</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input  value={this.state.location_map} onChange={this.handle_input_change} type="text" id="location_map" name="location_map" placeholder="Location" />
                                  <FormText className="help-block">Please enter the google map location</FormText>
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
                    <Button type='submit' hidden={!this.state.is_add_process_type} disabled={this.state.save_button_is_disabled} color="primary" onClick={this.save_item}><i className="fa fa-save"></i> Save</Button>{' '}
                    <Button type='submit' hidden={this.state.is_add_process_type} disabled={this.state.update_button_is_disabled} color="primary" onClick={this.update_item}><i className="fa fa-edit"></i>Update</Button>{' '}
                    <Button color="secondary" onClick={this.toggle_add_form}>Cancel</Button>
                  </ModalFooter>
                </Modal>
                <Modal size='lg' isOpen={this.state.detail_modal} toggle={this.toggle_detail_modal}
                  className={'modal-primary ' + this.props.className}>
                  <ModalHeader toggle={this.toggle_detail_modal}>Internship Detail</ModalHeader>
                  <ModalBody>

                    <Row>
                      <Col xs="12" md="12">
                        <Card>

                          <CardBody>
                            <Form action="" method="post" encType="multipart/form-data" className="form-horizontal">
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="name">Internship Name</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Label>{this.state.name}</Label>
                                  
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="country">Country</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <Label>{this.state.country}</Label>
                                  
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="city">City</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <Label>{this.state.city}</Label>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="address">Address</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <Label>{this.state.address}</Label>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="location_map">Location Map</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <Label>{this.state.location_map}</Label>
                                </Col>
                              </FormGroup>
                              
                              
                            </Form>

                          </CardBody>
                        </Card>
                      </Col>
                    </Row>

                  </ModalBody>
                  <ModalFooter>
                    <Button color="secondary" onClick={this.toggle_detail_modal}>Close</Button>
                  </ModalFooter>
                </Modal>
                <Alert hidden={this.state.has_alert_hidden} color={this.state.alert_type}>
                                    {this.state.alert_message}
                                  </Alert>
                <Table responsive>
                  <thead>
                    <tr>
                      <th>Internship</th>
                      <th>Address</th>
                      <th>City</th>
                      <th>Country</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {this.state.internships.map((data, i) => {
                      return (
                        <tr key={i}>
                          <td>{data.name}</td>
                          <td>{data.address}</td>
                          <td>{data.city}</td>
                          <td>{data.country}</td>
                          <td>
                          <Button size="sm" className='text-white' color="info" onClick={() => this.toggle_detail_modal(i)}><i className="fa fa-book"></i> Details</Button>
                            <Button size="sm" color="primary" onClick={() => this.load_item(i)}><i className="fa fa-pencil"></i></Button>
                            <Button size="sm" onClick={(e) => { if (window.confirm('Are you sure you wish to delete this item?')) this.delete_item(i) }} color="danger"><i className="fa fa-trash"></i></Button>
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
      </div>

    )
  }
}

export default Internship;
