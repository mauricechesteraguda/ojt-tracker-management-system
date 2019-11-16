import React, { Component } from 'react';
import {
  Badge,
  Row,
  Col,
  Card,
  CardHeader,
  CardBody,
  Table,
  Pagination,
  PaginationItem,
  PaginationLink,
  Button,
  Modal, ModalHeader, ModalBody, ModalFooter,
  Form,
  FormGroup,
  FormText,
  Label,
  Input,
  InputGroup,
  InputGroupAddon,
  InputGroupButton
} from 'reactstrap';
import axios from 'axios';


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
    }
    this.toggleAddForm = this.toggleAddForm.bind(this);
    this.handleInputChange = this.handleInputChange.bind(this);
    this.saveItem = this.saveItem.bind(this);
    this.updateItem = this.updateItem.bind(this);
    this.getUsers = this.getUsers.bind(this);
  }

  deleteItem(i){
    var self = this;
    axios.delete('api/users/'+this.state.users[i].id)
    .then(function (response) {
      console.log(response);
      self.getUsers()
      
    })
    .catch(function (error) {
      console.log(error);
    });
  }
  updateItem(){
    var self = this;
    let payload = {
      sr_code:this.state.code,
      name:this.state.name,
      role:this.state.role,
      email:this.state.email,
      password:this.state.password,
    }
    axios.post('/api/users/'+this.state.id, payload)
    .then(function (response) {
      console.log(response);
      self.getUsers()
      self.toggleAddForm()
    })
    .catch(function (error) {
      console.log(error);
    });

  }

  loadItem(i){
    this.toggleAddForm();
    this.setState({
      id: this.state.users[i].id,
      code: this.state.users[i].sr_code,
      name: this.state.users[i].name,
      role: this.state.users[i].role,
      email: this.state.users[i].email,
      updateButtonIsDisabled: true,

    })
    
    this.setState({isAddProcessType: false});
  }

  saveItem(){
    let payload = {
      sr_code:this.state.code,
      name:this.state.name,
      role:this.state.role,
      email:this.state.email,
      password:this.state.password,
    }
    axios.post('/api/users', payload)
    .then(function (response) {
      console.log(response);
      this.getUsers()
    })
    .catch(function (error) {
      console.log(error);
    });

  }

  handleInputChange(e) {
    // console.log(e.target.name)
    this.setState({ [e.target.name]: e.target.value });
    if ( e.target.value== '' ) {
      console.log('Incomplete form values!')
      this.state.saveButtonIsDisabled = true
      this.state.updateButtonIsDisabled = true
          
    }else{
     
          console.log('Complete form values!')
          this.state.saveButtonIsDisabled = false
          this.state.updateButtonIsDisabled = true
     
    }

    if (e.target.name == 'confirm_password') {
      if (this.state.password != e.target.value) {
        console.log('Password mismatch!')
        this.state.saveButtonIsDisabled = true
        this.state.updateButtonIsDisabled = true

      }else if (this.state.password == '' && e.target.value == '') {
        this.state.saveButtonIsDisabled = true
        this.state.updateButtonIsDisabled = true
        console.log('Password empty!')
      }
      else {
        console.log('Password matched!')
        this.state.saveButtonIsDisabled = false
        this.state.updateButtonIsDisabled = false
      }
    } else if (e.target.name == 'password') {
      if (this.state.confirm_password != e.target.value) {
        console.log('Password mismatch!')
        this.state.saveButtonIsDisabled = true
        this.state.updateButtonIsDisabled = true
      }else{
        if (this.state.confirm_password == '' && e.target.value == '') {
          this.state.saveButtonIsDisabled = true
          this.state.updateButtonIsDisabled = true
          console.log('Password empty!')
        }
        else {
          console.log('Password matched!')
          this.state.saveButtonIsDisabled = false
          this.state.updateButtonIsDisabled = false
        }
      }
    } 

    


  }

  toggleAddForm() {
    this.setState({
      id: '',
      code: '',
      name: '',
      role: '',
      email: '',
      updateButtonIsDisabled: true,
      addForm: !this.state.addForm,
      isAddProcessType: true
    });
    
  }

  storeUsersToState(data) {
    // console.log(data)
    this.setState({ users: data })
  }

  getUsers() {

    axios.get('/api/users')
      .then(res => {
        this.storeUsersToState(res.data.data)
      }).catch(err => {
        console.log(err)
      })
  }
  componentWillMount() {

    this.getUsers()

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
                  <ModalHeader toggle={this.toggleAddForm}>Add New User</ModalHeader>
                  <ModalBody>

                    <Row>
                      <Col xs="12" md="12">
                        <Card>

                          <CardBody>
                            <Form action="" method="post" encType="multipart/form-data" className="form-horizontal">
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="code">SRCODE / ID</Label>
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
                                  <Input value={this.state.role} onChange={this.handleInputChange} type="select" name="role" id="role">
                                    <option></option>
                                    <option>superuser</option>
                                    <option>coordinator</option>
                                    <option>student</option>

                                  </Input>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="email-input">Email</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input value={this.state.email} onChange={this.handleInputChange} type="email" id="email" name="email" placeholder="Enter Email" />
                                  <FormText className="help-block">Please enter your email</FormText>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="password">Password</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input value={this.state.password} onChange={this.handleInputChange} type="password" id="password" name="password" placeholder="Password" />
                                  <FormText className="help-block">Please enter a complex password</FormText>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="confirm_password">Confirm Password</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input value={this.state.confirm_password} onChange={this.handleInputChange} type="password" id="confirm_password" name="confirm_password" placeholder="Confirm Password" />
                                  <FormText className="help-block">Please confirm complex password</FormText>
                                </Col>
                              </FormGroup>


                            </Form>

                          </CardBody>
                        </Card>
                      </Col>
                    </Row>

                  </ModalBody>
                  <ModalFooter>
                    <Button hidden={!this.state.isAddProcessType} disabled={this.state.saveButtonIsDisabled} color="primary" onClick={this.saveItem}>Save</Button>{' '}
                    <Button hidden={this.state.isAddProcessType} disabled={this.state.updateButtonIsDisabled} color="primary" onClick={this.updateItem}>Update</Button>{' '}
                    <Button color="secondary" onClick={this.toggleAddForm}>Cancel</Button>
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
                            <Button color="primary" onClick={()=>this.loadItem(i)}>Update</Button>
                            <Button onClick={(e) => { if (window.confirm('Are you sure you wish to delete this item?')) this.deleteItem(i) } } color="danger">Delete</Button>
                          </td>
                        </tr>
                      )
                    })}
                    {/* <tr>
                      <td>Samppa Nori</td>
                      <td>2012/01/01</td>
                      <td>Member</td>
                      <td>
                        <Badge color="success">Active</Badge>
                      </td>
                    </tr> */}

                  </tbody>
                </Table>
                <Pagination>
                  <PaginationItem>
                    <PaginationLink previous href="#"></PaginationLink>
                  </PaginationItem>
                  <PaginationItem active>
                    <PaginationLink href="#">1</PaginationLink>
                  </PaginationItem>
                  <PaginationItem>
                    <PaginationLink href="#">2</PaginationLink>
                  </PaginationItem>
                  <PaginationItem>
                    <PaginationLink href="#">3</PaginationLink>
                  </PaginationItem>
                  <PaginationItem>
                    <PaginationLink href="#">4</PaginationLink>
                  </PaginationItem>
                  <PaginationItem>
                    <PaginationLink next href="#"></PaginationLink>
                  </PaginationItem>
                </Pagination>
              </CardBody>
            </Card>
          </Col>

        </Row>
      </div>

    )
  }
}

export default Users;
