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
import Paginations from "react-js-pagination";


class Users extends Component {
  constructor(props) {
    super(props)
    this.state = {
      users: [],
      addForm: false,
      id: '',
      code: '',
      name: '',
      role: '',
      email: '',
      password: '',
      confirm_password: '',
      isAddProcessType: true,
      saveButtonIsDisabled: true,
      updateButtonIsDisabled: true,
      has_alert_hidden: true,
      alert_type: 'danger',
      alert_message: '',
      activePage:1,
      itemsCountPerPage:1,
      totalItemsCount:1,
      userDetailModal: false,
      
    }
    this.toggleAddForm = this.toggleAddForm.bind(this);
    this.handleInputChange = this.handleInputChange.bind(this);
    this.saveItem = this.saveItem.bind(this);
    this.updateItem = this.updateItem.bind(this);
    this.getUsers = this.getUsers.bind(this);
    this.validateField = this.validateField.bind(this);
    this.deleteItem = this.deleteItem.bind(this);
    this.disableButtons = this.disableButtons.bind(this);
    this.handlePageChange = this.handlePageChange.bind(this);
    this.toggleUserDetailModal = this.toggleUserDetailModal.bind(this);
    
  }

  disableButtons(){
    this.setState(
      {
        saveButtonIsDisabled: true,
        updateButtonIsDisabled: true,
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
            this.disableButtons()
    
          } else if (this.state.confirm_password.length <=6 || this.state.password.length <=6){
            console.log('Password is too short!')
            this.setState({
              alert_message: 'Password is too short!',
              alert_type: 'danger',
              has_alert_hidden: false,
            })
            this.disableButtons()
          }else {
            console.log('Password matched!')
            this.setState({
              alert_message: 'Password matched!',
              alert_type: 'success',
              has_alert_hidden: false,
            })
            this.state.saveButtonIsDisabled = false
            this.state.updateButtonIsDisabled = false
          }
        }else{
          this.setState({
            alert_message: 'Email is invalid.',
            alert_type: 'danger',
            has_alert_hidden: false,
          })
          this.disableButtons()
        }

        break;

      default:
        break;
    }
    if (this.state.name =='' || this.state.role == '' || this.state.email == '' || this.state.password == '' || this.state.confirm_password == '' ) {
      console.log('Incomplete form values!')
      this.disableButtons()
    }
  }

  deleteItem(i) {
    var self = this;
    axios.delete('api/users/' + this.state.users[i].id)
      .then(function (response) {
        console.log(response);
        self.getUsers()

      })
      .catch(function (error) {
        console.log(error);
        alert('Deletion Failed. Contact your System Administrator')
      });
  }

  updateItem(e) {
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
        self.getUsers()
        self.toggleAddForm()
      })
      .catch(function (error) {
        console.log(error);
        alert('Deletion Failed. Contact your System Administrator')
      });

  }

  loadItem(i) {
    this.toggleAddForm();
    this.setState({
      id: this.state.users[i].id,
      code: this.state.users[i].sr_code,
      name: this.state.users[i].name,
      role: this.state.users[i].role,
      email: this.state.users[i].email,
      updateButtonIsDisabled: true,

    })

    this.setState({ isAddProcessType: false });
  }

  saveItem(e) {
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
        self.getUsers()
        self.toggleAddForm();
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

  handleInputChange(e) {
    var self = this;

    // clear alert status
    self.setState({
      alert_message: '',
      alert_type: 'primary',
      has_alert_hidden: true,
    })

    this.setState({ [e.target.name]: e.target.value });
    if (e.target.value == '' || (this.state.name =='' && this.state.role == '' && this.state.email == '' && this.state.password == '' && this.state.confirm_password == '' )) {
      console.log('Incomplete form values!')
      this.disableButtons()
    } else {
      console.log('Complete form values!')
      if (this.state.isAddProcessType) {
        this.setState(
          {
            saveButtonIsDisabled: false,
            updateButtonIsDisabled: true,
          }
        ) 
      } else {
        this.setState(
          {
            saveButtonIsDisabled: true,
            updateButtonIsDisabled: false,
          }
        ) 
      }
    }

    if (e.target.name == 'confirm_password') {
      if (this.state.password != e.target.value) {
        console.log('Password mismatch!')
        self.setState({
          alert_message: 'Password mismatch!',
          alert_type: 'danger',
          has_alert_hidden: false,
        })
        this.disableButtons()

      } else if (this.state.confirm_password.length <=6 || this.state.password.length <=6){
        console.log('Password is too short!')
        self.setState({
          alert_message: 'Password is too short!',
          alert_type: 'danger',
          has_alert_hidden: false,
        })
        this.disableButtons()

      }else if (this.state.password == '' && e.target.value == '') {
        this.disableButtons()

        console.log('Password empty!')
      }
      else {
        console.log('Password matched!')
        self.setState({
          alert_message: 'Password matched!',
          alert_type: 'success',
          has_alert_hidden: false,
        })
        this.state.saveButtonIsDisabled = false
        this.state.updateButtonIsDisabled = false
      }
    } else if (e.target.name == 'password') {
      if (this.state.confirm_password != e.target.value) {
        console.log('Password mismatch!')
        self.setState({
          alert_message: 'Password mismatch!',
          alert_type: 'danger',
          has_alert_hidden: false,
        })
        this.disableButtons()

      } else if (this.state.confirm_password.length <=6 || this.state.password.length <=6){
        console.log('Password is too short!')
        self.setState({
          alert_message: 'Password is too short!',
          alert_type: 'danger',
          has_alert_hidden: false,
        })
        this.disableButtons()

      }else {
        if (this.state.confirm_password == '' && e.target.value == '') {
          this.disableButtons()
          console.log('Password empty!')
        }
        else {
          console.log('Password matched!')
          self.setState({
            alert_message: 'Password matched!',
            alert_type: 'success',
            has_alert_hidden: false,
          })
          this.state.saveButtonIsDisabled = false
          this.state.updateButtonIsDisabled = false
        }
      }
    }

    
    this.validateField(e.target.name, e.target.value);



  }

  toggleAddForm() {
    this.setState({
      id: '',
      code: '',
      name: '',
      role: 'student',
      email: '',
      password:'',
      confirm_password: '',
      updateButtonIsDisabled: true,
      addForm: !this.state.addForm,
      isAddProcessType: true,
      alert_message: '',
      alert_type: 'primary',
      has_alert_hidden: true,
    });

  }
  toggleUserDetailModal(i=undefined){
    
    if (i >= 0 ) {
      this.setState({
        userDetailModal: !this.state.userDetailModal,
        id: this.state.users[i].id,
        code: this.state.users[i].sr_code,
        name: this.state.users[i].name,
        role: this.state.users[i].role,
        email: this.state.users[i].email,
        password:'********',
      });
    }else{
      this.setState({
        userDetailModal: !this.state.userDetailModal,
      });
    }
    
  }

  storeUsersToState(data) {
    // console.log(data)
    this.setState({ 
      users: data.data,
      activePage: data.meta.current_page,
      itemsCountPerPage:data.meta.per_page,
      totalItemsCount:data.meta.total,
     })
  }

  getUsers(pageNumber) {
    var self = this;
    axios.get('/api/users/?page='+ pageNumber)
      .then(res => {
        self.storeUsersToState(res.data)
      }).catch(err => {
        console.log(err)
        alert('Server disconnected.')
      })
  }

  componentWillMount() {

    this.getUsers()

  }

  handlePageChange(pageNumber) {
    console.log(`active page is ${pageNumber}`);
    this.getUsers(pageNumber)
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
                    <i className="fa fa-align-justify"></i> Users
                </Col>
                  <Col xs="6" lg="6">
                    <Button className="float-right fa fa-plus-circle" color="primary" onClick={this.toggleAddForm}> Add</Button>
                  </Col>
                </Row>
              </CardHeader>
              <CardBody>
                <Modal isOpen={this.state.addForm} toggle={this.toggleAddForm}
                  className={'modal-primary ' + this.props.className}>
                  <ModalHeader toggle={this.toggleAddForm}>{this.state.isAddProcessType ? 'Add New' : 'Update'} User</ModalHeader>
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
                                  <Label hidden={this.state.isAddProcessType}>{this.state.code}</Label>
                                  <Input hidden={!this.state.isAddProcessType} value={this.state.code} onChange={this.handleInputChange} type="text" id="code" name="code" placeholder="Text" />
                                  <FormText color="muted">Student / Faculty Code</FormText>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="name">Name</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input value={this.state.name} onChange={this.handleInputChange} type="text" id="name" name="name" placeholder="Text" />
                                  <FormText color="muted">Write your name</FormText>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="role">Role</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input  value={this.state.role} onChange={this.handleInputChange} type="select" name="role" id="role">
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
                                  <Input  value={this.state.email} onChange={this.handleInputChange} type="email" id="email" name="email" placeholder="Enter Email" />
                                  <FormText className="help-block">Please enter your email</FormText>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="password">Password</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input  value={this.state.password} onChange={this.handleInputChange} type="password" id="password" name="password" placeholder="Password" />
                                  <FormText className="help-block">Please enter a complex password</FormText>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="confirm_password">Confirm Password</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input  value={this.state.confirm_password} onChange={this.handleInputChange} type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" />
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
                    <Button type='submit' hidden={!this.state.isAddProcessType} disabled={this.state.saveButtonIsDisabled} color="primary" onClick={this.saveItem}>Save</Button>{' '}
                    <Button type='submit' hidden={this.state.isAddProcessType} disabled={this.state.updateButtonIsDisabled} color="primary" onClick={this.updateItem}>Update</Button>{' '}
                    <Button color="secondary" onClick={this.toggleAddForm}>Cancel</Button>
                  </ModalFooter>
                </Modal>
                <Modal size='lg' isOpen={this.state.userDetailModal} toggle={this.toggleUserDetailModal}
                  className={'modal-primary ' + this.props.className}>
                  <ModalHeader toggle={this.toggleUserDetailModal}>User Detail</ModalHeader>
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
                    <Button color="secondary" onClick={this.toggleUserDetailModal}>Cancel</Button>
                  </ModalFooter>
                </Modal>
                <Table responsive>
                  <thead>
                    <tr>
                      <th>SRCODE</th>
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
                          <Button className='text-white' color="info" onClick={() => this.toggleUserDetailModal(i)}>View Details</Button>
                            <Button color="primary" onClick={() => this.loadItem(i)}>Update</Button>
                            <Button onClick={(e) => { if (window.confirm('Are you sure you wish to delete this item?')) this.deleteItem(i) }} color="danger">Delete</Button>
                          </td>
                        </tr>
                      )
                    })}


                  </tbody>
                </Table>
                <Paginations
                    activePage={this.state.activePage}
                    itemsCountPerPage={this.state.itemsCountPerPage}
                    totalItemsCount={this.state.totalItemsCount}
                    pageRangeDisplayed={5}
                    onChange={this.handlePageChange}
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
