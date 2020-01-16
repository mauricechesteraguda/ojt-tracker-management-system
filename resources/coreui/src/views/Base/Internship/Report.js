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

class Report extends Component {
  constructor(props) {
    super(props)
    this.state = {
      reports: [],
      

      add_form: false,
      id: '',
      user_id: window.current_user_id,
      internship_id: this.props.internship_id,
      description: '',
      date: '',
      hours: 1,
      is_valid: '0',
      comment: '',
      updated_by: window.current_user_id,

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
    this.load_item = this.load_item.bind(this);
    this.update_item = this.update_item.bind(this);

    this.get_data = this.get_data.bind(this);
    this.check_inputs = this.check_inputs.bind(this);
    this.delete_item = this.delete_item.bind(this);
    this.disable_buttons = this.disable_buttons.bind(this);
    this.enable_button = this.enable_button.bind(this);
    this.handle_page_change = this.handle_page_change.bind(this);
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
    if (this.state.description == '' || this.state.date == '' || this.state.hours == '') {
      console.log('Incomplete form values!')
      this.disable_buttons()
    }else{
      this.enable_button()
    }
  }

  delete_item(i) {
    var self = this;
    axios.delete('api/reports/' + this.state.reports[i].id)
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
      description: self.state.description,
      date: self.state.date,
      hours: parseInt(self.state.hours),
      is_valid: self.state.is_valid,
      comment: self.state.comment,
      updated_by: self.state.updated_by,
    }
    axios.post('/api/reports/' + self.state.id, payload)
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
    if (this.props.internship_id) {
      this.setState({
        id: this.state.reports[i].id,
        internship_id: this.props.internship_id,
        description: this.state.reports[i].description,
        date: this.state.reports[i].date,
        hours: parseInt(this.state.reports[i].hours),
        is_valid: this.state.reports[i].is_valid,
        comment: this.state.reports[i].comment || '',
        updated_by: this.state.updated_by,
        update_button_is_disabled: true,
  
      })
  
    }
    this.setState({ is_add_process_type: false });
  }


  save_item(e) {
    e.preventDefault();
    
    var self = this;
    let payload = {
      internship_id: this.props.internship_id,
      description: this.state.description,
      date: this.state.date,
      hours: this.state.hours,
      is_valid: this.state.is_valid,
      comment: this.state.comment,
      updated_by: this.state.updated_by,

    }
    axios.post('/api/reports', payload)
      .then(function (response) {
        console.log(response);
        self.get_data()
        self.toggle_add_form();
      })
      .catch(function (error) {
        console.log(error.response.status);
        if (error.response.data.message.includes('Report already')) {
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
   
   axios.get('/api/reports/search/'+value+'/internship/'+this.props.internship_id)
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
        
          
          this.check_inputs();
          

        }, 500);
      }else{
        setTimeout(() => {
        
          
          this.check_inputs();
          

        }, 500);
      }

  }
    


  }

  toggle_add_form() {
    this.check_inputs();
    this.setState({
      id: '',
      internship_id: this.props.internship_id,
      description: '',
      date: '',
      hours: 1,
      is_valid: '0',
      comment: '',
      updated_by: this.state.updated_by,
      
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
      reports: data.data,

      active_page: data.meta.current_page,
      items_count_per_page:data.meta.per_page,
      total_items_count:data.meta.total,
     })
  }

  get_data(page_number) {
    var self = this;
    if (this.props.internship_id) {
      axios.get('/api/reports/internship/'+this.props.internship_id)
      .then(res => {
        self.store_data_to_state(res.data)
      }).catch(err => {
        console.log(err)
        alert('Server disconnected.')
      })
    }
    
  }

  get_users() {
    var self = this;
    axios.get('/api/users/internship/requirement')
      .then(res => {
        self.setState({users:res.data.data})
      }).catch(err => {
        console.log(err)
        alert('Server disconnected.')
      })
  }

  get_user(id) {
    for (let i = 0; i < this.state.users.length; i++) {
      
      if (this.state.users[i].id == id) {
        return this.state.users[i].first_name + ' ' + this.state.users[i].last_name  
      }
      
      
    }
  }

  componentDidUpdate(prevProps, prevState) {
    const { internship_id } = this.props;
    if (prevProps.internship_id !== internship_id) this.get_data();

  }

  componentDidMount() {

    this.get_users()

  }

  handle_page_change(page_number) {
    console.log(`active page is ${page_number}`);
    this.get_data(page_number)
  }

  render() {
    return (
      
        <Row hidden={this.props.is_hidden}>
          <Col hidden={!this.props.is_approved} xs="12" lg="12">
            <Card>
              <CardHeader>
                <Row>
                <Col xs="4" lg="4">
                  
                  <i className="fa fa-tasks"></i> Reports
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
                  <ModalHeader toggle={this.toggle_add_form}>{this.state.is_add_process_type ? 'Add New' : 'Update'} Report</ModalHeader>
                  <ModalBody>

                    <Row>
                      <Col xs="12" md="12">
                        <Card>

                          <CardBody>
                            <Form action="" method="post" encType="multipart/form-data" className="form-horizontal">

                            
                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="description">Description</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input value={this.state.description} onChange={this.handle_input_change} type="text" id="description" name="description" placeholder="Text" />
                                  <FormText color="muted">Please enter the description</FormText>
                                </Col>
                              </FormGroup>

                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="date">Date</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input value={this.state.date} onChange={this.handle_input_change} type="date" id="date" name="date" placeholder="Text" />
                                  <FormText color="muted">Please enter the date</FormText>
                                </Col>
                              </FormGroup>

                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="hours">Hours</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input value={this.state.hours} min='1' onChange={this.handle_input_change} type="number" id="hours" name="hours" placeholder="Hours" />
                                  <FormText color="muted">Please enter the hours</FormText>
                                </Col>
                              </FormGroup>



                              <Can
                                role={user.role}
                                perform="user-report:validate"
                                yes={() =>   <FormGroup row>
                                  <Col md="3">
                                    <Label htmlFor="is_valid">Valid?</Label>
                                  </Col>
                                  <Col xs="12" md="9">
  
                                  
                                    <Input value={this.state.is_valid}  onChange={this.handle_input_change} type="select" name="is_valid" id="is_valid">
                                    <option value="0">No</option>
                                      <option value="1">Yes</option>
                                      </Input>
                                  </Col>
                                </FormGroup>}
                                no={() =>  <div></div>
                                  }
                                />
                              

                              <FormGroup row>
                                <Col md="3">
                                  <Label htmlFor="comment">Comment</Label>
                                </Col>
                                <Col xs="12" md="9">
                                  <Input value={this.state.comment} onChange={this.handle_input_change} type="text" id="comment" name="comment" placeholder="Text" />
                                  <FormText color="muted">Please enter your comment</FormText>
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
                      <th>Description</th>
                      <th>Date</th>
                      <th>Hours</th>
                      <th>Status</th>
                      <th>Remarks</th>
                      <th>Last Updated by</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {this.state.reports.map((data, i) => {
                      return (
                        <tr key={i}>
                          <td>{data.description}</td>
                          <td>{data.date}</td>
                          <td>{data.hours}</td>
                          <td><Label><Badge color={data.is_valid == '1' ? 'success':'danger'} >{data.is_valid == '1' ? 'valid':'pending'}</Badge></Label></td>
                          <td>{data.comment}</td>
                          <td>
                          
                          {this.get_user(data.updated_by)}

                          </td>
                          
                          <td>
                          <Button size="sm" color="primary" onClick={() => this.load_item(i)}><i className="fa fa-pencil"></i></Button>
                            <Button  size="sm" onClick={(e) => { if (window.confirm('Are you sure you wish to delete this item?')) this.delete_item(i) }} color="danger"><i className="fa fa-trash"></i></Button>
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

export default Report;
