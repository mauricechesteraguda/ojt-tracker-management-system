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


class Users extends Component {
  constructor(props) {
    super(props)
    this.state = {
      users: [],

      add_form: false,
      id: '',
      code: '',
      first_name: '',
      last_name: '',
      name: '',
      role: '',
      email: '',
      password: '',
      confirm_password: '',

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
    this.validateField = this.validateField.bind(this);
    this.delete_item = this.delete_item.bind(this);
    this.disable_buttons = this.disable_buttons.bind(this);
    this.handle_page_change = this.handle_page_change.bind(this);
    this.go_back = this.go_back.bind(this);
    this.toggle_detail_page = this.toggle_detail_page.bind(this);
    this.handle_search_input_change = this.handle_search_input_change.bind(this);
    
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

  validateField(fieldName, value) {
   var emailValid = false

    switch(fieldName) {
      case 'email':
        emailValid = value.match(/^([\w.%+-]+)@([\w-]+\.)+([\w]{2,})$/i);
        if(emailValid){
          this.setState({
            alert_message: '',
            alert_type: 'primary',
            has_alert_hidden: true,
          })
          if (this.state.password != this.state.confirm_password) {
            console.log('Password mismatch!')
            this.setState({
              alert_message: 'Password mismatch!',
              alert_type: 'danger',
              has_alert_hidden: false,
            })
            this.disable_buttons()
    
          } else if (this.state.confirm_password.length <=6 || this.state.password.length <=6){
            console.log('Password is too short!')
            this.setState({
              alert_message: 'Password is too short!',
              alert_type: 'danger',
              has_alert_hidden: false,
            })
            this.disable_buttons()
          }else {
            console.log('Password matched!')
            this.setState({
              alert_message: 'Password matched!',
              alert_type: 'success',
              has_alert_hidden: false,
            })
            this.state.save_button_is_disabled = false
            this.state.update_button_is_disabled = false
          }
        }else{
          this.setState({
            alert_message: 'Email is invalid.',
            alert_type: 'danger',
            has_alert_hidden: false,
          })
          this.disable_buttons()
        }

        break;

      default:
        break;
    }
    if (this.state.first_name =='' || this.state.last_name =='' || this.state.name =='' || this.state.role == '' || this.state.email == '' || this.state.password == '' || this.state.confirm_password == '' ) {
      console.log('Incomplete form values!')
      this.disable_buttons()
    }
  }

  delete_item(i) {
    var self = this;
    axios.delete('api/users/' + this.state.users[i].id)
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
      sr_code: this.state.code,
      first_name: this.state.first_name,
      last_name: this.state.last_name,
      name: this.state.name,
      role: this.state.role,
      email: this.state.email,
      password: this.state.password,
    }
    axios.post('/api/users/' + this.state.id, payload)
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
    if (this.state.is_detail_page) {
      this.setState({
        id: this.state.users[this.state.current_index].id,
        code: this.state.users[this.state.current_index].sr_code,
        first_name: this.state.users[this.state.current_index].first_name,
        last_name: this.state.users[this.state.current_index].last_name,
        name: this.state.users[this.state.current_index].name,
        role: this.state.users[this.state.current_index].role,
        email: this.state.users[this.state.current_index].email,
        update_button_is_disabled: true,
  
      })
  
      
    }else{
      this.setState({
        id: this.state.users[i].id,
        code: this.state.users[i].sr_code,
        first_name: this.state.users[i].first_name,
        last_name: this.state.users[i].last_name,
        name: this.state.users[i].name,
        role: this.state.users[i].role,
        email: this.state.users[i].email,
        update_button_is_disabled: true,
  
      })
  

    }
    this.setState({ is_add_process_type: false });
  }
  

  save_item(e) {
    e.preventDefault();
    
    var self = this;
    let payload = {
      sr_code: this.state.code,
      first_name: this.state.first_name,
      last_name: this.state.last_name,
      name: this.state.name,
      role: this.state.role,
      email: this.state.email,
      password: this.state.password,
    }
    axios.post('/api/users', payload)
      .then(function (response) {
        console.log(response);
        self.get_data()
        self.toggle_add_form();
      })
      .catch(function (error) {
        console.log(error.response.status);
        if (error.response.data.message.includes('Username')) {
          self.setState({
            alert_message: error.response.data.message,
            alert_type: 'danger',
            has_alert_hidden: false,
          })
        }else{
          self.setState({
            alert_message: 'Email already exist.',
            alert_type: 'danger',
            has_alert_hidden: false,
          })
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
   
   axios.get('/api/users/search/'+value)
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
    if (e.target.value == '' || 
      (this.state.first_name =='' && 
      this.state.last_name =='' && 
      this.state.name =='' && 
      this.state.role == '' && 
      this.state.email == '' && 
      this.state.password == '' 
      && this.state.confirm_password == '' )) {
      console.log('Incomplete form values!')
      this.disable_buttons()
    } else {
      console.log('Complete form values!')
      if (this.state.is_add_process_type) {
        this.setState(
          {
            save_button_is_disabled: false,
            update_button_is_disabled: true,
          }
        ) 
      } else {
        this.setState(
          {
            save_button_is_disabled: true,
            update_button_is_disabled: false,
          }
        ) 
      }
    }

    if (e.target.name == 'confirm_password') {
      if (this.state.password != e.target.value) {
        console.log('Password mismatch!')
        this.setState({
          alert_message: 'Password mismatch!',
          alert_type: 'danger',
          has_alert_hidden: false,
        })
        this.disable_buttons()

      } else if (this.state.confirm_password.length <=6 || this.state.password.length <=6){
        console.log('Password is too short!')
        this.setState({
          alert_message: 'Password is too short!',
          alert_type: 'danger',
          has_alert_hidden: false,
        })
        this.disable_buttons()

      }else if (this.state.password == '' && e.target.value == '') {
        this.disable_buttons()

        console.log('Password empty!')
      }
      else {
        console.log('Password matched!')
        this.setState({
          alert_message: 'Password matched!',
          alert_type: 'success',
          has_alert_hidden: false,
        })
        this.state.save_button_is_disabled = false
        this.state.update_button_is_disabled = false
      }
    } else if (e.target.name == 'password') {
      if (this.state.confirm_password != e.target.value) {
        console.log('Password mismatch!')
        this.setState({
          alert_message: 'Password mismatch!',
          alert_type: 'danger',
          has_alert_hidden: false,
        })
        this.disable_buttons()

      } else if (this.state.confirm_password.length <=6 || this.state.password.length <=6){
        console.log('Password is too short!')
        this.setState({
          alert_message: 'Password is too short!',
          alert_type: 'danger',
          has_alert_hidden: false,
        })
        this.disable_buttons()

      }else {
        if (this.state.confirm_password == '' && e.target.value == '') {
          this.disable_buttons()
          console.log('Password empty!')
        }
        else {
          console.log('Password matched!')
          this.setState({
            alert_message: 'Password matched!',
            alert_type: 'success',
            has_alert_hidden: false,
          })
          this.state.save_button_is_disabled = false
          this.state.update_button_is_disabled = false
        }
      }
    }

    
    this.validateField(e.target.name, e.target.value);



  }

  toggle_add_form() {
    this.setState({
      id: '',
      code: '',
      first_name: '',
      last_name: '',
      name: '',
      role: 'student',
      email: '',
      password:'',
      confirm_password: '',

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
        id: this.state.users[i].id,
        code: this.state.users[i].sr_code,
        first_name: this.state.users[i].first_name,
        last_name: this.state.users[i].last_name,
        name: this.state.users[i].name,
        role: this.state.users[i].role,
        email: this.state.users[i].email,
        password:'********',
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
      users: data.data,

      active_page: data.meta.current_page,
      items_count_per_page:data.meta.per_page,
      total_items_count:data.meta.total,
     })
  }

  get_data(page_number) {
    var self = this;
    axios.get('/api/users/?page='+ page_number)
      .then(res => {
        self.store_data_to_state(res.data)
      }).catch(err => {
        console.log(err)
        alert('Server disconnected.')
      })
  }

  componentDidMount() {

    this.get_data()

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
                  
                  <i className="fa fa-users"></i> Users
                  </Col>
                  <Col xs="4" lg="4">
                  <Can
                  role={user.role}
                  perform="user:search"
                  yes={() => <Input
                    type="text"
                    value={this.state.search_value}
                    
                    name="search"
                    placeholder="Search..."
                    onChange={this.handle_search_input_change}
                    />}
                  no={() => <div></div>}
                  />
                  
                    </Col>
                    
                
                
                  <Col xs="4" lg="4">
                  <Can
                  role={user.role}
                  perform="user:add"
                  yes={() =>  <Button className="float-lg-right" color="primary" onClick={this.toggle_add_form}><i className="fa fa-plus-circle"></i> Add</Button>}
                  no={() => <div></div>}
                  />
                       
                        
                  </Col>
                </Row>
              </CardHeader>
              <CardBody>
                <Modal isOpen={this.state.add_form} toggle={this.toggle_add_form}
                  className={'modal-primary ' + this.props.className}>
                  <ModalHeader toggle={this.toggle_add_form}>{this.state.is_add_process_type ? 'Add New' : 'Update'} User</ModalHeader>
                  <ModalBody>

                    <Row>
                      <Col xs="12" md="12">
                        <Card>

                          <CardBody>
                            <Form action="" method="post" encType="multipart/form-data" className="form-horizontal">
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="code">SRCODE / Username</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Label hidden={this.state.is_add_process_type}>{this.state.code}</Label>
                                  <Input hidden={!this.state.is_add_process_type} value={this.state.code} onChange={this.handle_input_change} type="text" id="code" name="code" placeholder="Text" />
                                  <FormText color="muted">Student / Faculty Code</FormText>
                                </Col>
                              </FormGroup>
                              
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="first_name">First Name</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input value={this.state.first_name} onChange={this.handle_input_change} type="text" id="first_name" name="first_name" placeholder="Text" />
                                  <FormText color="muted">Write your first name</FormText>
                                </Col>
                              </FormGroup>

                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="last_name">Last Name</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input value={this.state.last_name} onChange={this.handle_input_change} type="text" id="last_name" name="last_name" placeholder="Text" />
                                  <FormText color="muted">Write your last_name</FormText>
                                </Col>
                              </FormGroup>

                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="name">Middle Name</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input value={this.state.name} onChange={this.handle_input_change} type="text" id="name" name="name" placeholder="Text" />
                                  <FormText color="muted">Write your name</FormText>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="role">Role</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input  value={this.state.role} onChange={this.handle_input_change} type="select" name="role" id="role">

                                  <Can
                  role={user.role}
                  perform="user-role:change"
                  yes={() =>   <div><option>student</option>
                    <option>superuser</option>
                  <option>coordinator</option></div>}
                  no={() =>  <option>{user.role}</option>
                    }
                  />
                                 
                                    

                                  </Input>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="email-input">Email</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input  value={this.state.email} onChange={this.handle_input_change} type="email" id="email" name="email" placeholder="Enter Email" />
                                  <FormText className="help-block">Please enter your email</FormText>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="password">Password</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input  value={this.state.password} onChange={this.handle_input_change} type="password" id="password" name="password" placeholder="Password" />
                                  <FormText className="help-block">Please enter a complex password</FormText>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="confirm_password">Confirm Password</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input  value={this.state.confirm_password} onChange={this.handle_input_change} type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" />
                                  <FormText className="help-block">Please confirm complex password</FormText>
                                  <Alert hidden={this.state.has_alert_hidden} color={this.state.alert_type}>
                                    {this.state.alert_message}
                                  </Alert>
                                </Col>
                              </FormGroup>


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
                      <th>SRCODE/Username</th>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Role</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {this.state.users.map((data, i) => {
                      return (
                        <tr key={i}>
                          <td>{data.sr_code}</td>
                          <td>{data.last_name}, {data.first_name} {data.name}</td>
                          <td>{data.email}</td>
                          <td>{data.role}</td>
                          <td>
                          <Button size="sm" className='text-white' color="info" onClick={() => this.toggle_detail_page(i)}><i className="fa fa-book"></i> Details</Button>
                            <Button size="sm" color="primary" onClick={() => this.load_item(i)}><i className="fa fa-pencil"></i></Button>

                            <Can
                  role={user.role}
                  perform="user:delete"
                  yes={() =>  <Button size="sm" onClick={(e) => { if (window.confirm('Are you sure you wish to delete this item?')) this.delete_item(i) }} color="danger"><i className="fa fa-trash"></i></Button>}
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
          <Col xs="12" lg="12">
            <Card>
              <CardHeader>
                <Row>
                <Col xs="4" lg="4">
                  
                  <i className="fa fa-user-circle"></i> User Detail
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
                                  <Label htmlFor="code">SRCODE / Username</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Label>{this.state.code}</Label>
                                  
                                </Col>
                              </FormGroup>

                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="first_name">First Name</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <Label>{this.state.first_name}</Label>
                                  
                                </Col>
                              </FormGroup>

                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="last_name">Last Name</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <Label>{this.state.last_name}</Label>
                                  
                                </Col>
                              </FormGroup>


                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="name">Middle Name</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <Label>{this.state.name}</Label>
                                  
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="role">Role</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <Label>{this.state.role}</Label>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="email">Email</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <Label>{this.state.email}</Label>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="password">Password</Label>
                                </Col>
                                <Col xs="12" md="9">
                                <Label>{this.state.password}</Label>
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

export default Users;
