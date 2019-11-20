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


class Users extends Component {
  constructor(props) {
    super(props)
    this.state = {
      users: [],

      add_form: false,
      id: '',
      code: '',
      name: '',
      role: '',
      email: '',
      password: '',
      confirm_password: '',

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
    this.validateField = this.validateField.bind(this);
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
    if (this.state.name =='' || this.state.role == '' || this.state.email == '' || this.state.password == '' || this.state.confirm_password == '' ) {
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
      })
      .catch(function (error) {
        console.log(error);
        alert('Update Failed. Contact your System Administrator')
      });

  }

  load_item(i) {
    this.toggle_add_form();
    this.setState({
      id: this.state.users[i].id,
      code: this.state.users[i].sr_code,
      name: this.state.users[i].name,
      role: this.state.users[i].role,
      email: this.state.users[i].email,
      update_button_is_disabled: true,

    })

    this.setState({ is_add_process_type: false });
  }

  save_item(e) {
    e.preventDefault();
    
    var self = this;
    let payload = {
      sr_code: this.state.code,
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
   }
   axios.get('/api/users/search/'+value)
      .then(function (response) {
        console.log(response);
        self.store_data_to_state(response.data)
      })
      .catch(function (error) {
        console.log(error);
        alert('Search Failed. Contact your System Administrator.')
      });
  }


  handle_input_change(e) {
     // clear alert status
    this.setState({
      alert_message: '',
      alert_type: 'primary',
      has_alert_hidden: true,
    })

    this.setState({ [e.target.name]: e.target.valuee.target.value });
    if (e.target.value == '' || 
      (this.state.name =='' && 
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
  toggle_detail_modal(i=undefined){
    
    if (i >= 0 ) {
      this.setState({
        detail_modal: !this.state.detail_modal,
        id: this.state.users[i].id,
        code: this.state.users[i].sr_code,
        name: this.state.users[i].name,
        role: this.state.users[i].role,
        email: this.state.users[i].email,
        password:'********',
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

  componentWillMount() {

    this.get_data()

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
                  <Col xs="6" lg="6">
                  <Row>
                  <Col xs="2" lg="2">
                    <i className="fa fa-align-justify"></i> Users
                    </Col>
                    <Col xs="10" lg="10">
                    <InputGroup>
                      <Input 
                      type="text"
                      value={this.state.search_value}
                      id="search"
                      name="search"
                      placeholder="Search..."
                      onChange={this.handle_search_input_change}
                      />
                    </InputGroup>
                    </Col>
                </Row>
                </Col>
                  <Col xs="6" lg="6">
                    <Button className="float-right fa fa-plus-circle" color="primary" onClick={this.toggle_add_form}> Add</Button>
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
                                  <Label htmlFor="name">Name</Label>
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
                                  <option>student</option>
                                    <option>superuser</option>
                                    <option>coordinator</option>
                                    

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
                    <Button type='submit' hidden={!this.state.is_add_process_type} disabled={this.state.save_button_is_disabled} color="primary" onClick={this.save_item}>Save</Button>{' '}
                    <Button type='submit' hidden={this.state.is_add_process_type} disabled={this.state.update_button_is_disabled} color="primary" onClick={this.update_item}>Update</Button>{' '}
                    <Button color="secondary" onClick={this.toggle_add_form}>Cancel</Button>
                  </ModalFooter>
                </Modal>
                <Modal size='lg' isOpen={this.state.detail_modal} toggle={this.toggle_detail_modal}
                  className={'modal-primary ' + this.props.className}>
                  <ModalHeader toggle={this.toggle_detail_modal}>User Detail</ModalHeader>
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
                                  <Label>{this.state.code}</Label>
                                  
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="name">Name</Label>
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

                  </ModalBody>
                  <ModalFooter>
                    <Button color="secondary" onClick={this.toggle_detail_modal}>Close</Button>
                  </ModalFooter>
                </Modal>
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
                          <td>{data.name}</td>
                          <td>{data.email}</td>
                          <td>{data.role}</td>
                          <td>
                          <Button className='text-white' color="info" onClick={() => this.toggle_detail_modal(i)}>View Details</Button>
                            <Button color="primary" onClick={() => this.load_item(i)}>Update</Button>
                            <Button onClick={(e) => { if (window.confirm('Are you sure you wish to delete this item?')) this.delete_item(i) }} color="danger">Delete</Button>
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

export default Users;
