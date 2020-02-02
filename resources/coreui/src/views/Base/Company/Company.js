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
  InputGroup,
  InputGroupAddon

} from 'reactstrap';
import axios from 'axios';
import Paginations from "react-js-pagination";
import Can from '../../../Can';


class Company extends Component {
  constructor(props) {
    super(props)
    this.state = {
      companies: [],

      add_form: false,
      id: '',
      name: '',
      country: '',
      province: '',
      city: '',
      address: '',
      location_map: '',

      is_add_process_type: true,
      save_button_is_disabled: true,
      update_button_is_disabled: true,
      is_detail_page: false,

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
    this.update_item = this.update_item.bind(this);
    this.get_data = this.get_data.bind(this);
    this.check_inputs = this.check_inputs.bind(this);
    this.delete_item = this.delete_item.bind(this);
    this.disable_buttons = this.disable_buttons.bind(this);
    this.handle_page_change = this.handle_page_change.bind(this);
    this.handle_search_input_change = this.handle_search_input_change.bind(this);
    this.toggle_detail_page = this.toggle_detail_page.bind(this);
    this.go_back = this.go_back.bind(this);

    this.load_local_storage = this.load_local_storage.bind(this);
    this.store_to_local_storage = this.store_to_local_storage.bind(this);

    this.store_data_to_state = this.store_data_to_state.bind(this);
  
  }

  load_local_storage(key){
    if (localStorage.getItem(key)) {
      this.setState({[key]:JSON.parse(localStorage.getItem(key))})
    }
  }

  store_to_local_storage(key,value){

    localStorage.setItem(key,JSON.stringify(value))

  }

  componentDidMount() {

    this.get_data()

  }

  go_back(){
    if (this.state.is_detail_page == true) {
      this.setState({is_detail_page:false})
    }
    
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

    if (this.state.name == '' || this.state.country == '' || this.state.province == '' || this.state.city == '' || this.state.address == '' || this.state.location_map == '') {
      console.log('Incomplete form values!')
      this.disable_buttons()
    } else {
      if (!this.state.location_map.includes('https://www.google.com/maps/embed?pb=')) {
        console.log('Invalid google embeded link.')
        this.disable_buttons()
        this.setState({
          alert_message: 'Invalid google embeded link.',
          alert_type: 'danger',
          has_alert_hidden: false,
        })

      } else {
        this.enable_button()
      }
      this.enable_button()
    }
  }

  delete_item(i) {
    var self = this;
    axios.delete('api/companies/' + this.state.companies[i].id)
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
      name: this.state.name,
      country: this.state.country,
      province: this.state.province,
      city: this.state.city,
      address: this.state.address,
      location_map: this.state.location_map,
    }
    axios.post('/api/companies/' + this.state.id, payload)
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
      id: this.state.companies[i].id,
      name: this.state.companies[i].name,
      country: this.state.companies[i].country,
      province: this.state.companies[i].province,
      city: this.state.companies[i].city,
      address: this.state.companies[i].address,
      location_map: this.state.companies[i].location_map,
      update_button_is_disabled: true,

    })

    this.setState({ is_add_process_type: false });
  }

  save_item(e) {
    e.preventDefault();
    
    var self = this;
    let payload = {
      name: this.state.name,
      country: this.state.country,
      province: this.state.province,
      city: this.state.city,
      address: this.state.address,
      location_map: this.state.location_map,
    }
    axios.post('/api/companies', payload)
      .then(function (response) {
        console.log(response);
        self.get_data()
        self.toggle_add_form();
      })
      .catch(function (error) {
        console.log(error.response.status);
        if (error.response.data.message.includes('Company already')) {
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
   
   axios.get('/api/companies/search/'+value)
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

    if (typeof e.target != undefined) {

      this.setState({ [e.target.name]: e.target.value });

      if (e.target.value == '') {
        this.disable_buttons();
        setTimeout(() => {

          this.check_inputs(e.target.name, e.target.value);
        }, 500);
      } else {
        this.check_inputs(e.target.name, e.target.value);
      }
    }

  }

  toggle_add_form() {
    this.setState({
      id: '',
      name: '',
      country: '',
      province:'',
      city: '',
      address: '',
      location_map:'',

      update_button_is_disabled: true,
      add_form: !this.state.add_form,
      is_add_process_type: true,

      alert_message: '',
      alert_type: 'primary',
      has_alert_hidden: true,
    });

  }
  toggle_detail_page(i=undefined){
    
    if (i >= 0 ) {
      this.setState({
        is_detail_page: true,
        current_index:i,
        id: this.state.companies[i].id,
        name: this.state.companies[i].name,
        country: this.state.companies[i].country,
        province: this.state.companies[i].province,
        city: this.state.companies[i].city,
        address: this.state.companies[i].address,
        location_map: this.state.companies[i].location_map,
      });
    }else{
      this.setState({
        is_detail_page: false,
      });
    }
    
  }

  store_data_to_state(data) {
    // console.log(data)
    this.setState({ 
      companies: data.data,

      active_page: data.meta.current_page,
      items_count_per_page:data.meta.per_page,
      total_items_count:data.meta.total,
     })

     this.store_to_local_storage('companies',data.data)

  }

  get_data(page_number) {
    var self = this;
    axios.get('/api/companies/?page='+ page_number)
      .then(res => {
        self.store_data_to_state(res.data)
      }).catch(err => {
        console.log(err)
        self.load_local_storage('companies')
        // alert('Server disconnected.')
      })
  }



  handle_page_change(page_number) {
    console.log(`active page is ${page_number}`);
    this.get_data(page_number)
  }

  render() {
    return (
      <div className="animated fadeIn">
        <Row hidden={this.state.is_detail_page}>
          <Col xs="12" lg="12">
            <Card>
              <CardHeader>
                <Row>
                <Col xs="4" lg="4">
                  
                  <i className="fa fa-bank"></i> Company
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
                  perform="company:add"
                  yes={() => <Button className="float-lg-right" color="primary" onClick={this.toggle_add_form}><i className="fa fa-plus-circle"></i> Add</Button>}
                  no={() => <div></div>}
                  />
                        
                        
                  </Col>
                </Row>
              </CardHeader>
              <CardBody>
                <Modal isOpen={this.state.add_form} toggle={this.toggle_add_form}
                  className={'modal-primary ' + this.props.className}>
                  <ModalHeader toggle={this.toggle_add_form}>{this.state.is_add_process_type ? 'Add New' : 'Update'} Company</ModalHeader>
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
                                  <FormText color="muted">Company Name</FormText>
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
                                  <Label htmlFor="province">Province</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input  value={this.state.province} onChange={this.handle_input_change} type="text" name="province" id="province"/>
                                  
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="city">City/Municipality</Label>
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
                                  <FormText className="help-block">Copy and paste the embed link from google maps</FormText>
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
                
                <Alert hidden={this.state.has_alert_hidden} color={this.state.alert_type}>
                                    {this.state.alert_message}
                                  </Alert>
                <Table responsive>
                  <thead>
                    <tr>
                      <th>Company</th>
                      <th>Address</th>
                      <th>City</th>
                      <th>Province</th>
                      <th>Country</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {this.state.companies.map((data, i) => {
                      return (
                        <tr key={i}>
                          <td>{data.name}</td>
                          <td>{data.address}</td>
                          <td>{data.city}</td>
                          <td>{data.province}</td>
                          <td>{data.country}</td>
                          <td>
                          <Button size="sm" className='text-white' color="info" onClick={() => this.toggle_detail_page(i)}><i className="fa fa-book"></i> Details</Button>

                          <Can
                  role={user.role}
                  perform="company:edit"
                  yes={() => <Button size="sm" color="primary" onClick={() => this.load_item(i)}><i className="fa fa-pencil"></i></Button>}
                  no={() => <div></div>}
                  />
                            
                            <Can
                  role={user.role}
                  perform="company:edit"
                  yes={() => <Button size="sm" onClick={(e) => { if (window.confirm('Are you sure you wish to delete this item?')) this.delete_item(i) }} color="danger"><i className="fa fa-trash"></i></Button>}
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

        <Row hidden={!this.state.is_detail_page}>
                      <Col xs="12" md="12">
                        <Card>
                        <CardHeader>
                <Row>
                <Col xs="4" lg="4">
                  
                  <i className="fa fa-bank"></i> Company Detail
                  </Col>
                  <Col xs="4" lg="4">
                  
                  </Col>
                  <Col xs="4" lg="4">
                        
                        
                        <Button className="float-right" color="primary" onClick={this.go_back}><i className="fa fa-hand-o-left"></i> Back</Button>
                        
                  </Col>
                </Row>
              </CardHeader>

                          <CardBody>
                            <Form action="" method="post" encType="multipart/form-data" className="form-horizontal">
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="name">Company Name</Label>
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
                                  <Label htmlFor="province">Province</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <Label>{this.state.province}</Label>
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
                                <iframe src={this.state.location_map} width="100%" height="100%" frameBorder="0"  allowFullScreen></iframe>
                               
					   		
                                </Col>
                              </FormGroup>


					  
                            </Form>

                          </CardBody>
                        </Card>
                      </Col>
                    </Row>

                  
      </div>

    )
  }
}

export default Company;
