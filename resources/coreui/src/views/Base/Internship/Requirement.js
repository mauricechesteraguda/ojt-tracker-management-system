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
  Badge,
  

} from 'reactstrap';
import axios from 'axios';
// import Select from 'react-select';
import Paginations from "react-js-pagination";



class Requirement extends Component {
  constructor(props) {
    super(props)
    this.state = {
      requirements: [],
      

      add_form: false,
      id: '',
      user_id: window.current_user_id,
      internship_id: this.props.internship_id,
      requirement: '',

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
  
    this.handle_input_change = this.handle_input_change.bind(this);

    this.get_data = this.get_data.bind(this);

    this.handle_page_change = this.handle_page_change.bind(this);
    this.handle_search_input_change = this.handle_search_input_change.bind(this);
  
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
   
   axios.get('/api/requirements/search/'+value+'/internship/'+this.props.internship_id)
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
    // this.setState({
    //   alert_message: '',
    //   alert_type: 'primary',
    //   has_alert_hidden: true,
    // })

    // this.setState({ [e.target.name]: e.target.value });
    
    // if (e.target.value == '') {
    //   this.disable_buttons();
    //   setTimeout(() => {
      
    //     this.check_inputs(e.target.name, e.target.value);
    //   }, 500);
    // }else{
    //   this.check_inputs(e.target.name, e.target.value);
    // }
    
    if (e.target.checked) {
      alert("Checked")
    }else{
      alert("Unchecked")
    }


  }


  store_data_to_state(data) {
    // console.log(data)
    this.setState({ 
      requirements: data.data,

      active_page: data.meta.current_page,
      items_count_per_page:data.meta.per_page,
      total_items_count:data.meta.total,
     })
  }

  get_data(page_number) {
    var self = this;
    if (this.props.internship_id) {
      axios.get('/api/requirements/internship/'+this.props.internship_id)
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
      
        
          <Col hidden={this.props.is_hidden} xs="6" lg="6">
            <Card>
              <CardHeader>
                <Row>
                <Col xs="4" lg="4">
                  
                  <i className="fa fa-tasks"></i> Requirements
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
                        
                        
                  </Col>
                </Row>
              </CardHeader>
              <CardBody>
                
                <Alert hidden={this.state.has_alert_hidden} color={this.state.alert_type}>
                                    {this.state.alert_message}
                                  </Alert>
                <Table responsive>
                  <thead>
                    <tr>
                      <th>Requirement</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    {this.state.requirements.map((data, i) => {
                      return (
                        <tr key={i}>
                          <td>{data.requirement_category.name}</td>
                          
                          <td><Label><Badge color={data.is_approved == '1' ? 'success':'danger'} >{data.is_approved == '1' ? 'verified':'pending'}</Badge></Label></td>
                          <td>
                            <Input type="checkbox" hidden={this.props.is_approved} onChange={this.handle_input_change}></Input>
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
          
      

    )
  }
}

export default Requirement;
