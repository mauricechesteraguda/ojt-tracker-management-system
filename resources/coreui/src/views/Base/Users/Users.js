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
      primary: false,
    }
    this.togglePrimary = this.togglePrimary.bind(this);
  }
  togglePrimary() {
    this.setState({
      primary: !this.state.primary
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
                    <Button className="float-right fa fa-plus-circle" color="primary" onClick={this.togglePrimary}> Add</Button>
                  </Col>
                </Row>
              </CardHeader>
              <CardBody>
                <Modal isOpen={this.state.primary} toggle={this.togglePrimary}
                  className={'modal-primary ' + this.props.className}>
                  <ModalHeader toggle={this.togglePrimary}>Add New User</ModalHeader>
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
                                  <Input type="text" id="code" name="text-input" placeholder="Text" />
                                  <FormText color="muted">Student / Faculty Code</FormText>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="name">Name</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input type="text" id="name" name="text-input" placeholder="Text" />
                                  <FormText color="muted">Write your name</FormText>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="role">Role</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input type="select" name="role" id="role">
                                  <option></option>
                                  <option>admin</option>
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
                                  <Input type="email" id="email" name="email" placeholder="Enter Email" />
                                  <FormText className="help-block">Please enter your email</FormText>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="password">Password</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input type="password" id="password" name="password" placeholder="Password" />
                                  <FormText className="help-block">Please enter a complex password</FormText>
                                </Col>
                              </FormGroup>
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="confirm_password">Confirm Password</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input type="password" id="confirm_password" name="password-input" placeholder="Confirm Password" />
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
                    <Button color="primary" onClick={this.togglePrimary}>Save</Button>{' '}
                    <Button color="secondary" onClick={this.togglePrimary}>Cancel</Button>
                  </ModalFooter>
                </Modal>
                <Table responsive>
                  <thead>
                    <tr>
                      <th>SRCODE</th>
                      <th>Name</th>
                      <th>Email</th>
                      <th>Role</th>
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
